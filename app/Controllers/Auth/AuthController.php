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
        'email_verified' => 1,
        'email_verified_at' => date('Y-m-d H:i:s'),
        'verification_token' => null,
        'status' => 'active'
    ]);

    return $this->response->setJSON([
        'status' => 'Email verified successfully'
    ]);
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

    // 1️⃣ Get JSON data (consistent with login)
    $data = $this->request->getJSON(true) ?? [];

    // 2️⃣ Validate required fields
    $name     = trim($data['name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Name, email and password are required']);
    }

    // 3️⃣ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Invalid email format']);
    }

    // 4️⃣ Validate password length
    if (strlen($password) < 8) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Password must be at least 8 characters']);
    }

    // 5️⃣ Check if email already exists
    if ($userModel->where('email', $email)->first()) {
        return $this->response
            ->setStatusCode(409)
            ->setJSON(['error' => 'Email already registered']);
    }

    // 6️⃣ Generate verification token
    $verificationToken = bin2hex(random_bytes(32));
    $db = \Config\Database::connect();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 7️⃣ Insert user (only include columns that exist in the table)
    $insertData = [
        'name'  => $name,
        'email' => $email,
    ];
    $insertData[$db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password'] = $passwordHash;
    if ($db->fieldExists('role', 'users')) {
        $insertData['role'] = 'student';
    }
    if ($db->fieldExists('status', 'users')) {
        $insertData['status'] = 'active';
    }
    if ($db->fieldExists('email_verified', 'users')) {
        $insertData['email_verified'] = 1;
    }
    if ($db->fieldExists('verification_token', 'users')) {
        $insertData['verification_token'] = $verificationToken;
    }

    $inserted = $userModel->insert($insertData);

    // 8️⃣ Check if insert failed
    if (!$inserted) {
        return $this->response
            ->setStatusCode(500)
            ->setJSON(['error' => 'Registration failed, please try again']);
    }

    // 9️⃣ Build verification link (for optional email verification)
    $verificationLink = base_url("api/auth/verify-email/" . $verificationToken);

    // 🔟 TODO: Send verification email
    // Example: $this->sendVerificationEmail($email, $verificationLink);

    return $this->response
        ->setStatusCode(201)
        ->setJSON([
            'status'  => 'registered',
            'message' => 'Registration successful. Please check your email to verify your account.',
            // Remove this line in production - only for testing without email
            'verify_link' => $verificationLink
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
    $userModel = new UserModel();
    $jwtService = new JWTService();
    $db = \Config\Database::connect();

    $data = $this->request->getJSON(true) ?? [];
    if (empty($data['email']) || empty($data['password'])) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Email and password are required']);
    }

    $user = $userModel->where('email', $data['email'])->first();

    // 1️⃣ Check email + password (support both 'password' and 'password_hash' column names)
    $storedPassword = $user['password'] ?? $user['password_hash'] ?? '';
    if (!$user || !password_verify($data['password'], $storedPassword)) {
        return $this->response
            ->setStatusCode(401)
            ->setJSON(['error' => 'Invalid credentials']);
    }

    $loginSurface = trim((string) ($data['login_surface'] ?? 'user_login'));
    if ($loginSurface === 'temp_login' && $db->fieldExists('temp_password_source', 'users')) {
        $src = isset($user['temp_password_source']) ? (string) $user['temp_password_source'] : '';
        if ($src === 'forgot') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Forgot-password accounts must sign in on the main sign-in page, not this first-time helper.']);
        }
    }

    // 2️⃣ Check email verified (if column exists)
    if (isset($user['email_verified']) && !$user['email_verified']) {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['error' => 'Please verify your email first']);
    }

    // 3️⃣ Check account status (if column exists)
    if (isset($user['status']) && $user['status'] !== 'active') {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['error' => 'Account not active']);
    }

    // 4️⃣ Update last login time (if column exists)
    if ($db->fieldExists('last_login_at', 'users')) {
        $userModel->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }

    // 5️⃣ Generate Access Token
    $accessToken = $jwtService->generateToken($user);

    // 6️⃣ Generate Refresh Token
    $refreshToken = bin2hex(random_bytes(64));
    if ($db->tableExists('refresh_tokens')) {
        $db->table('refresh_tokens')->insert([
            'user_id'   => $user['id'],
            'token'     => hash('sha256', $refreshToken),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);
    }

    // Frontend expects token and user – role must come from DB so admin panel shows for all admin/hr users
    $role = isset($user['role']) ? (string) $user['role'] : 'student';
    $forcePasswordChange = isset($user['force_password_change']) ? (int) $user['force_password_change'] : 0;
    $userPayload = [
        'id'    => (int) $user['id'],
        'name'  => $user['name'] ?? '',
        'email' => $user['email'] ?? '',
        'role'  => $role,
        'phone' => isset($user['phone']) ? (string) $user['phone'] : null,
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
        $db = \Config\Database::connect();
        $jwtService = new JWTService();

        $data = $this->request->getJSON(true);

        $hashedToken = hash('sha256', $data['refresh_token']);

        $tokenRecord = $db->table('refresh_tokens')
            ->where('token', $hashedToken)
            ->get()
            ->getRowArray();

        if (!$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid or expired refresh token']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($tokenRecord['user_id']);

        if (!$user) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'User not found']);
        }

        $newAccessToken = $jwtService->generateToken($user);

        return $this->response->setJSON([
            'access_token' => $newAccessToken
        ]);
    }

    public function logout()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getJSON(true);

        if (!isset($data['refresh_token'])) {
            return $this->response->setJSON(['status' => 'logged out']);
        }

        $hashedToken = hash('sha256', $data['refresh_token']);

        $db->table('refresh_tokens')
            ->where('token', $hashedToken)
            ->delete();

        return $this->response->setJSON(['status' => 'logged out']);
    }
    
    /**
     * Pre-register an inactive user (payment happens later).
     *
     * POST /api/auth/pre-register
     */
    public function preRegister()
    {
        $data = $this->request->getJSON(true) ?? [];

        $name = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $phone = trim((string) ($data['phone'] ?? ''));

        if (empty($name) || empty($email)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Name and email are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid email format']);
        }

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
        $data = $this->request->getJSON(true) ?? [];

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $activationToken = trim((string) ($data['activation_token'] ?? ''));

        $activation = new UserActivationService();
        $result = $activation->activateByToken($email, $activationToken);

        if (!$result['ok']) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => $result['error']]);
        }

        return $this->response->setJSON($result['payload']);
    }

    /**
     * Request admin access (created as inactive until Super Admin approves).
     *
     * POST /api/auth/request-admin
     */
    public function requestAdmin()
    {
        $data = $this->request->getJSON(true) ?? [];

        $name = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $phone = trim((string) ($data['phone'] ?? ''));

        if (empty($name) || empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Name, email and password are required']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid email format']);
        }
        if (strlen($password) < 8) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Password must be at least 8 characters']);
        }

        $userModel = new UserModel();
        $db = \Config\Database::connect();

        $role = 'admin';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $existing = $userModel->where('email', $email)->first();

        $updates = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'role' => $role,
            'status' => 'inactive',
            'email_verified' => 1,
            'activation_token' => null,
            'force_password_change' => 0,
        ];

        if (!$db->fieldExists('phone', 'users')) {
            unset($updates['phone']);
        }
        if (!$db->fieldExists('force_password_change', 'users')) {
            unset($updates['force_password_change']);
        }

        if ($db->fieldExists('password_hash', 'users')) {
            $updates['password_hash'] = $passwordHash;
        } elseif ($db->fieldExists('password', 'users')) {
            $updates['password'] = $passwordHash;
        }

        if ($existing) {
            $userModel->update($existing['id'], $updates);
        } else {
            $userModel->insert($updates);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Change password for an authenticated user.
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
            $jwt = new JWTService();
            $decoded = $jwt->verifyToken($token);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid or expired token']);
        }

        $uid = (int) ($decoded->uid ?? 0);
        if (!$uid) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid token']);
        }

        $data = $this->request->getJSON(true) ?? [];
        $oldPassword = (string) ($data['old_password'] ?? '');
        $newPassword = (string) ($data['new_password'] ?? '');

        if (empty($oldPassword) || empty($newPassword)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Old and new password are required']);
        }
        if (strlen($newPassword) < 6) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'New password must be at least 6 characters']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($uid);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $storedPassword = $user['password'] ?? $user['password_hash'] ?? '';
        if (!$storedPassword || !password_verify($oldPassword, $storedPassword)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Old password incorrect']);
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $db = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

        $updatePw = [
            $passwordField => $passwordHash,
            'force_password_change' => 0,
        ];
        if ($db->fieldExists('forgot_password_expires_at', 'users')) {
            $updatePw['forgot_password_expires_at'] = null;
        }
        if ($db->fieldExists('temp_password_source', 'users')) {
            $updatePw['temp_password_source'] = null;
        }
        $userModel->update($uid, $updatePw);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * POST /api/auth/reset-password/{token}  { "password": "..." }
     * Token is the raw value from the email link; only its SHA-256 hash is stored.
     */
    public function resetPassword($token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Token required']);
        }

        $data = $this->request->getJSON(true) ?? [];
        $newPassword = (string) ($data['password'] ?? '');
        if (strlen($newPassword) < 8) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Password must be at least 8 characters']);
        }

        $tokenHash = hash('sha256', $token);
        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $tokenHash)->first();
        if (!$user) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Invalid or expired link']);
        }

        $exp = $user['reset_expires'] ?? null;
        if ($exp === null || $exp === '' || strtotime((string) $exp) < time()) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Invalid or expired link']);
        }

        $db = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $updates = [
            $passwordField        => $passwordHash,
            'reset_token'         => null,
            'reset_expires'       => null,
        ];
        if ($db->fieldExists('force_password_change', 'users')) {
            $updates['force_password_change'] = 0;
        }
        if ($db->fieldExists('forgot_password_expires_at', 'users')) {
            $updates['forgot_password_expires_at'] = null;
        }
        if ($db->fieldExists('temp_password_source', 'users')) {
            $updates['temp_password_source'] = null;
        }

        $userModel->update($user['id'], $updates);

        return $this->response->setJSON([
            'ok'      => true,
            'message' => 'Password updated. You can sign in now.',
        ]);
    }

    /**
     * Forgot password: emails a one-time link to set a new password (no temporary password).
     *
     * POST /api/auth/forgot-password  { "email": "..." }
     */
    public function forgotPassword()
    {
        $data = $this->request->getJSON(true) ?? [];
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        $generic = [
            'ok'      => true,
            'message' => 'If an account exists for that email, we sent a link to set a new password.',
        ];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'A valid email is required']);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user || (($user['status'] ?? '') !== 'active')) {
            return $this->response->setJSON($generic);
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $updates = [
            'reset_token'   => $tokenHash,
            'reset_expires' => $expiresAt,
        ];
        $db = \Config\Database::connect();
        if ($db->fieldExists('forgot_password_expires_at', 'users')) {
            $updates['forgot_password_expires_at'] = null;
        }

        $userModel->update($user['id'], $updates);

        $resetPageUrl = base_url('reset-password') . '?token=' . rawurlencode($rawToken);
        $expiresHuman = date('Y-m-d H:i', strtotime($expiresAt));

        $txn = new TransactionalEmailService();
        $txn->sendPasswordResetLink(
            $email,
            (string) ($user['name'] ?? ''),
            $resetPageUrl,
            $expiresHuman
        );

        return $this->response->setJSON($generic);
    }


    /**
     * Admin can reset any user password.
     * POST /api/admin/reset-user-password  { "user_id": 5, "new_password": "..." }
     */
    public function adminResetUserPassword()
    {
        $data     = $this->request->getJSON(true) ?? [];
        $targetId = (int) ($data['user_id'] ?? 0);
        $newPass  = (string) ($data['new_password'] ?? '');

        if (!$targetId || strlen($newPass) < 6) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'user_id and new_password (min 6 chars) are required']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($targetId);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

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

}