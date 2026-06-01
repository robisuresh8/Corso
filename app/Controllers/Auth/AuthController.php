<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\JWTService;
use App\Services\UserActivationService;
use App\Services\TransactionalEmailService;

class AuthController extends BaseController
{
    public function verifyEmail($token)
    {
        $userModel = new UserModel();
        $user = $userModel->where('verification_token', $token)->first();

        if (!$user) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Invalid token']);
        }

        $userModel->update($user['id'], [
            'email_verified'     => 1,
            'email_verified_at'  => date('Y-m-d H:i:s'),
            'verification_token' => null,
            'status'             => 'active',
        ]);

        return $this->response->setJSON(['status' => 'Email verified successfully']);
    }

    public function register()
    {
        try {
            return $this->doRegister();
        } catch (\Throwable $e) {
            log_message('error', 'Auth register: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Registration failed. ' . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Please try again.')]);
        }
    }

    private function doRegister()
    {
        $userModel = new UserModel();
        $data      = $this->request->getJSON(true) ?? [];

        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Name, email and password are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid email format']);
        }
        if (strlen($password) < 8) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Password must be at least 8 characters']);
        }
        if ($userModel->where('email', $email)->first()) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Email already registered']);
        }

        $verificationToken = bin2hex(random_bytes(32));
        $db                = \Config\Database::connect();
        $passwordHash      = password_hash($password, PASSWORD_DEFAULT);

        $insertData = ['name' => $name, 'email' => $email];
        $insertData[$db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password'] = $passwordHash;
        if ($db->fieldExists('role', 'users'))               $insertData['role']               = 'student';
        if ($db->fieldExists('status', 'users'))             $insertData['status']             = 'active';
        if ($db->fieldExists('email_verified', 'users'))     $insertData['email_verified']     = 1;
        if ($db->fieldExists('verification_token', 'users')) $insertData['verification_token'] = $verificationToken;

        $inserted = $userModel->insert($insertData);

        if (!$inserted) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Registration failed, please try again']);
        }

        $verificationLink = base_url('api/auth/verify-email/' . $verificationToken);

        return $this->response->setStatusCode(201)->setJSON([
            'status'      => 'registered',
            'message'     => 'Registration successful. Please check your email to verify your account.',
            'verify_link' => $verificationLink,
        ]);
    }

    public function login()
    {
        try {
            return $this->doLogin();
        } catch (\Throwable $e) {
            log_message('error', 'Auth login: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Login failed. ' . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Please try again.')]);
        }
    }

    private function doLogin()
    {
        $userModel  = new UserModel();
        $jwtService = new JWTService();
        $db         = \Config\Database::connect();

        $data = $this->request->getJSON(true) ?? [];
        if (empty($data['email']) || empty($data['password'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Email and password are required']);
        }

        $user           = $userModel->where('email', $data['email'])->first();
        $storedPassword = $user['password'] ?? $user['password_hash'] ?? '';

        if (!$user || !password_verify($data['password'], $storedPassword)) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid credentials']);
        }

        $loginSurface = trim((string) ($data['login_surface'] ?? 'user_login'));
        if ($loginSurface === 'temp_login' && $db->fieldExists('temp_password_source', 'users')) {
            $src = isset($user['temp_password_source']) ? (string) $user['temp_password_source'] : '';
            if ($src === 'forgot') {
                return $this->response->setStatusCode(403)->setJSON([
                    'error' => 'Forgot-password accounts must sign in on the main sign-in page, not this first-time helper.',
                ]);
            }
        }

        if (isset($user['email_verified']) && !$user['email_verified']) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Please verify your email first']);
        }

        if (isset($user['status']) && $user['status'] !== 'active') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Account not active']);
        }

        if ($db->fieldExists('last_login_at', 'users')) {
            $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        }

        $accessToken  = $jwtService->generateToken($user);
        $refreshToken = bin2hex(random_bytes(64));

        if ($db->tableExists('refresh_tokens')) {
            $db->table('refresh_tokens')->insert([
                'user_id'    => $user['id'],
                'token'      => hash('sha256', $refreshToken),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            ]);
        }

        $role                = isset($user['role']) ? (string) $user['role'] : 'student';
        $forcePasswordChange = isset($user['force_password_change']) ? (int) $user['force_password_change'] : 0;

        $userPayload = [
            'id'                   => (int) $user['id'],
            'name'                 => $user['name'] ?? '',
            'email'                => $user['email'] ?? '',
            'role'                 => $role,
            'phone'                => isset($user['phone']) ? (string) $user['phone'] : null,
            'force_password_change' => $forcePasswordChange,
        ];
        if ($db->fieldExists('temp_password_source', 'users')) {
            $userPayload['temp_password_source'] = isset($user['temp_password_source']) && $user['temp_password_source'] !== null
                ? (string) $user['temp_password_source']
                : null;
        }

        return $this->response->setJSON([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token'         => $accessToken,
            'user'          => $userPayload,
        ]);
    }

    public function refresh()
    {
        $db         = \Config\Database::connect();
        $jwtService = new JWTService();
        $data       = $this->request->getJSON(true);

        $hashedToken = hash('sha256', $data['refresh_token']);
        $tokenRecord = $db->table('refresh_tokens')->where('token', $hashedToken)->get()->getRowArray();

        if (!$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid or expired refresh token']);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($tokenRecord['user_id']);

        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User not found']);
        }

        return $this->response->setJSON(['access_token' => $jwtService->generateToken($user)]);
    }

    public function logout()
    {
        $db   = \Config\Database::connect();
        $data = $this->request->getJSON(true);

        if (!isset($data['refresh_token'])) {
            return $this->response->setJSON(['status' => 'logged out']);
        }

        $db->table('refresh_tokens')->where('token', hash('sha256', $data['refresh_token']))->delete();

        return $this->response->setJSON(['status' => 'logged out']);
    }

    /**
     * Pre-register a user before payment.
     * Stores in users table with status=inactive and activation_token.
     *
     * POST /api/auth/pre-register
     */
    public function preRegister()
    {
        $data  = $this->request->getJSON(true) ?? [];
        $name  = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $phone = trim((string) ($data['phone'] ?? ''));

        if (empty($name) || empty($email)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Name and email are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid email format']);
        }

<<<<<<< HEAD
        $userModel = new UserModel();
        $db        = \Config\Database::connect();

        // Active user already exists — don't overwrite
        $activeUser = $userModel->where('email', $email)->where('status', 'active')->first();
        if ($activeUser) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'An active account already exists with this email. Please log in.',
            ]);
        }

        // Fresh token every time — ensures DB and frontend are always in sync
        $activationToken = bin2hex(random_bytes(32));
        $tempPassword    = bin2hex(random_bytes(8));
        $passwordField   = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

        $existingInactive = $userModel->where('email', $email)->where('status', 'inactive')->first();

        if ($existingInactive) {
            // Update existing inactive user with fresh token
            $updateData = [
                'name'             => $name,
                'activation_token' => $activationToken,
                'updated_at'       => date('Y-m-d H:i:s'),
            ];
            if ($phone !== '' && $db->fieldExists('phone', 'users')) {
                $updateData['phone'] = $phone;
            }
            $userModel->update($existingInactive['id'], $updateData);
        } else {
            // Insert new inactive user
            $insertData = [
                'name'             => $name,
                'email'            => $email,
                'role'             => 'student',
                'status'           => 'inactive',
                'email_verified'   => 1,
                'activation_token' => $activationToken,
                $passwordField     => password_hash($tempPassword, PASSWORD_DEFAULT),
            ];
            if ($phone !== '' && $db->fieldExists('phone', 'users')) {
                $insertData['phone'] = $phone;
            }
            if ($db->fieldExists('force_password_change', 'users')) {
                $insertData['force_password_change'] = 1;
            }
            $userModel->insert($insertData);
=======
        $userModel    = new UserModel();
        $visitorModel = new \App\Models\VisitorModel();

        // Active user wapas register kare toh uska account inactive mat karo
        $activeUser = $userModel->where('email', $email)->where('status', 'active')->first();
        if ($activeUser) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'An active account already exists with this email. Please log in.'
            ]);
        }

        $activationToken = bin2hex(random_bytes(32));

        // Visitors table mein check karo — existing visitor ho toh token reuse karo
        $existingVisitor = $visitorModel->where('email', $email)->first();
        if ($existingVisitor && !empty($existingVisitor['cookie_token'])) {
            $activationToken = $existingVisitor['cookie_token'];
            $visitorModel->update($existingVisitor['id'], [
                'name'          => $name,
                'phone'         => $phone !== '' ? $phone : null,
                'is_registered' => 0,
                'last_active'   => date('Y-m-d H:i:s'),
                'expires_at'    => date('Y-m-d H:i:s', strtotime('+7 days')),
            ]);
        } else {
            $visitorModel->insert([
                'name'          => $name,
                'email'         => $email,
                'phone'         => $phone !== '' ? $phone : null,
                'cookie_token'  => $activationToken,
                'is_registered' => 0,
                'last_active'   => date('Y-m-d H:i:s'),
                'expires_at'    => date('Y-m-d H:i:s', strtotime('+7 days')),
            ]);
>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
        }

        return $this->response->setJSON([
            'user' => [
                'name'   => $name,
                'email'  => $email,
                'phone'  => $phone !== '' ? $phone : null,
                'status' => 'inactive',
            ],
            'activation_token' => $activationToken,
        ]);
    }

    /**
     * Consume activation token after payment succeeds.
     *
     * POST /api/auth/activate
     */
    public function activate()
    {
        $data            = $this->request->getJSON(true) ?? [];
        $email           = strtolower(trim((string) ($data['email'] ?? '')));
        $activationToken = trim((string) ($data['activation_token'] ?? ''));

        $activation = new UserActivationService();
        $result     = $activation->activateByToken($email, $activationToken);

        if (!$result['ok']) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $result['error']]);
        }

        return $this->response->setJSON($result['payload']);
    }

    /**
     * Request admin access.
     *
     * POST /api/auth/request-admin
     */
    public function requestAdmin()
    {
        $data     = $this->request->getJSON(true) ?? [];
        $name     = trim((string) ($data['name'] ?? ''));
        $email    = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $phone    = trim((string) ($data['phone'] ?? ''));

        if (empty($name) || empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Name, email and password are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid email format']);
        }
        if (strlen($password) < 8) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Password must be at least 8 characters']);
        }

        $userModel    = new UserModel();
        $db           = \Config\Database::connect();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $updates = [
            'name'             => $name,
            'email'            => $email,
            'role'             => 'admin',
            'status'           => 'inactive',
            'email_verified'   => 1,
            'activation_token' => null,
            'force_password_change' => 0,
        ];
        if ($phone !== '' && $db->fieldExists('phone', 'users')) $updates['phone'] = $phone;
        if (!$db->fieldExists('force_password_change', 'users')) unset($updates['force_password_change']);

        $updates[$db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password'] = $passwordHash;

        $existing = $userModel->where('email', $email)->first();
        if ($existing) {
            $userModel->update($existing['id'], $updates);
        } else {
            $userModel->insert($updates);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Change password for authenticated user.
     *
     * POST /api/auth/change-password
     */
    public function changePassword()
    {
        $authHeader = (string) $this->request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Authorization required']);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        try {
            $jwt     = new JWTService();
            $decoded = $jwt->verifyToken($token);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid or expired token']);
        }

        $uid = (int) ($decoded->uid ?? 0);
        if (!$uid) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid token']);
        }

        $data        = $this->request->getJSON(true) ?? [];
        $oldPassword = (string) ($data['old_password'] ?? '');
        $newPassword = (string) ($data['new_password'] ?? '');

        if (empty($oldPassword) || empty($newPassword)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Old and new password are required']);
        }
        if (strlen($newPassword) < 6) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'New password must be at least 6 characters']);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($uid);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $storedPassword = $user['password'] ?? $user['password_hash'] ?? '';
        if (!$storedPassword || !password_verify($oldPassword, $storedPassword)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Old password incorrect']);
        }

        $db            = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';
        $updatePw      = [$passwordField => password_hash($newPassword, PASSWORD_DEFAULT), 'force_password_change' => 0];

        if ($db->fieldExists('forgot_password_expires_at', 'users')) $updatePw['forgot_password_expires_at'] = null;
        if ($db->fieldExists('temp_password_source', 'users'))        $updatePw['temp_password_source']       = null;

        $userModel->update($uid, $updatePw);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * POST /api/auth/reset-password/{token}
     */
    public function resetPassword($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Token required']);
        }

        $data        = $this->request->getJSON(true) ?? [];
        $newPassword = (string) ($data['password'] ?? '');
        if (strlen($newPassword) < 8) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Password must be at least 8 characters']);
        }

        $tokenHash = hash('sha256', $token);
        $userModel = new UserModel();
        $user      = $userModel->where('reset_token', $tokenHash)->first();

        if (!$user) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid or expired link']);
        }

        $exp = $user['reset_expires'] ?? null;
        if ($exp === null || $exp === '' || strtotime((string) $exp) < time()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid or expired link']);
        }

        $db            = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';
        $updates       = [$passwordField => password_hash($newPassword, PASSWORD_DEFAULT), 'reset_token' => null, 'reset_expires' => null];

        if ($db->fieldExists('force_password_change', 'users'))      $updates['force_password_change']      = 0;
        if ($db->fieldExists('forgot_password_expires_at', 'users')) $updates['forgot_password_expires_at'] = null;
        if ($db->fieldExists('temp_password_source', 'users'))       $updates['temp_password_source']       = null;

        $userModel->update($user['id'], $updates);

        return $this->response->setJSON(['ok' => true, 'message' => 'Password updated. You can sign in now.']);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword()
    {
        $data  = $this->request->getJSON(true) ?? [];
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        $generic = ['ok' => true, 'message' => 'If an account exists for that email, we sent a link to set a new password.'];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'A valid email is required']);
        }

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        if (!$user || (($user['status'] ?? '') !== 'active')) {
            return $this->response->setJSON($generic);
        }

        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $updates = ['reset_token' => $tokenHash, 'reset_expires' => $expiresAt];
        $db      = \Config\Database::connect();
        if ($db->fieldExists('forgot_password_expires_at', 'users')) $updates['forgot_password_expires_at'] = null;

        $userModel->update($user['id'], $updates);

        $resetPageUrl = base_url('reset-password') . '?token=' . rawurlencode($rawToken);
        $expiresHuman = date('Y-m-d H:i', strtotime($expiresAt));

        $txn = new TransactionalEmailService();
        $txn->sendPasswordResetLink($email, (string) ($user['name'] ?? ''), $resetPageUrl, $expiresHuman);

        return $this->response->setJSON($generic);
    }

<<<<<<< HEAD
    /**
     * POST /api/admin/reset-user-password
=======

    /**
     * Admin can reset any user password.
     * POST /api/admin/reset-user-password  { "user_id": 5, "new_password": "..." }
>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
     */
    public function adminResetUserPassword()
    {
        $data     = $this->request->getJSON(true) ?? [];
        $targetId = (int) ($data['user_id'] ?? 0);
        $newPass  = (string) ($data['new_password'] ?? '');

        if (!$targetId || strlen($newPass) < 6) {
<<<<<<< HEAD
            return $this->response->setStatusCode(400)->setJSON(['error' => 'user_id and new_password (min 6 chars) are required']);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($targetId);
=======
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'user_id and new_password (min 6 chars) are required']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($targetId);
>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

<<<<<<< HEAD
        $db            = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

        $updates = [$passwordField => password_hash($newPass, PASSWORD_DEFAULT), 'force_password_change' => 1];
        if ($db->fieldExists('temp_password_source', 'users'))       $updates['temp_password_source']       = 'admin_reset';
        if ($db->fieldExists('forgot_password_expires_at', 'users')) $updates['forgot_password_expires_at'] = null;

        $userModel->update($targetId, $updates);

        return $this->response->setJSON(['ok' => true, 'message' => 'Password reset successfully']);
    }
=======
        $db = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

        $updates = [
            $passwordField          => password_hash($newPass, PASSWORD_DEFAULT),
            'force_password_change' => 1,
        ];
        if ($db->fieldExists('temp_password_source', 'users')) {
            $updates['temp_password_source'] = 'admin_reset';
        }
        if ($db->fieldExists('forgot_password_expires_at', 'users')) {
            $updates['forgot_password_expires_at'] = null;
        }

        $userModel->update($targetId, $updates);
        return $this->response->setJSON(['ok' => true, 'message' => 'Password reset successfully']);
    }

>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
}