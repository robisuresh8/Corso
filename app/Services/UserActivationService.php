<?php

namespace App\Services;

use App\Models\UserModel;

class UserActivationService
{
    /**
     * Activate a pre-registered user after successful payment.
     *
     * @param array<string, mixed>|null $paymentContext Razorpay order_id, payment_id, amount_paise, currency, course_name, paid_at
     * @return array{ok: true, payload: array<string, mixed>}|array{ok: false, error: string}
     */
    public function activateByToken(string $email, string $activationToken, ?array $paymentContext = null): array
    {
        $email           = strtolower(trim($email));
        $activationToken = trim($activationToken);

        if ($email === '' || $activationToken === '') {
            return ['ok' => false, 'error' => 'Email and activation token are required'];
        }

<<<<<<< HEAD
        $userModel = new UserModel();

        // Users table mein dhundo by email + activation_token
        $user = $userModel
=======
        $userModel    = new UserModel();
        $visitorModel = new \App\Models\VisitorModel();

        // Pehle visitors table mein dhundo
        $visitor = $visitorModel
>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
            ->where('email', $email)
            ->where('cookie_token', $activationToken)
            ->first();

<<<<<<< HEAD
        if (!$user) {
            $existingByEmail = $userModel->where('email', $email)->first();
            if ($existingByEmail) {
                if (empty($existingByEmail['activation_token'])) {
                    return ['ok' => false, 'error' => 'Account already activated. Please log in.'];
                }
                log_message('debug', 'Activation token mismatch. email=' . $email .
                    ' db_token=' . $existingByEmail['activation_token'] .
                    ' provided_token=' . $activationToken);
            } else {
                log_message('debug', 'Activation failed: no user found for email=' . $email);
            }
            return ['ok' => false, 'error' => 'Invalid Token'];
        }

        // Already active check
        if (($user['status'] ?? '') === 'active') {
            return ['ok' => false, 'error' => 'Account already activated. Please log in.'];
        }

        // Temp password generate karo
        $tempPassword  = bin2hex(random_bytes(6));
        $db            = \Config\Database::connect();
        $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

        $updateData = [
            'status'           => 'active',
            'activation_token' => null,
            'email_verified'   => 1,
            $passwordField     => password_hash($tempPassword, PASSWORD_DEFAULT),
        ];
=======
        // tempPassword outer scope mein define karo (dono flows ke liye)
        $tempPassword = bin2hex(random_bytes(6));

        if (!$visitor) {
            // Fallback: purana flow — users table mein bhi check karo
            $user = $userModel
                ->where('email', $email)
                ->where('activation_token', $activationToken)
                ->first();

            if (!$user) {
                $existingByEmail = $userModel->where('email', $email)->first();
                if ($existingByEmail) {
                    log_message('debug', 'Activation failed: token mismatch. Email=' . $email);
                    if (empty($existingByEmail['activation_token'])) {
                        return ['ok' => false, 'error' => 'Account already activated or token expired. Please log in.'];
                    }
                } else {
                    log_message('debug', 'Activation failed: No user/visitor found with email=' . $email);
                }
                return ['ok' => false, 'error' => 'Invalid activation token'];
            }
        } else {
            // Visitor mila! Users table mein move karo
            $db            = \Config\Database::connect();
            $passwordHash  = password_hash($tempPassword, PASSWORD_DEFAULT);
            $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

            $newUser = [
                'name'                  => $visitor['name'],
                'email'                 => $visitor['email'],
                'phone'                 => $visitor['phone'] ?? null,
                'role'                  => 'student',
                'status'                => 'active',
                'email_verified'        => 1,
                'activation_token'      => null,
                'force_password_change' => 1,
                $passwordField          => $passwordHash,
            ];

            if (!$db->fieldExists('phone', 'users'))                  unset($newUser['phone']);
            if (!$db->fieldExists('force_password_change', 'users'))  unset($newUser['force_password_change']);

            // Check karo users mein already hai ya nahi (duplicate protection)
            $existingUser = $userModel->where('email', $email)->first();
            if ($existingUser) {
                $userModel->update($existingUser['id'], array_merge($newUser, ['status' => 'active']));
                $user = $userModel->find($existingUser['id']);
            } else {
                $userId = $userModel->insert($newUser);
                $user   = $userModel->find($userId);
            }

            // Visitor ko registered mark karo
            $visitorModel->update($visitor['id'], ['is_registered' => 1]);
        }

        // Agar visitor se naya user create hua toh password already set hai
        // Purane users table flow ke liye update karo
        if (!isset($visitor)) {
            $db = \Config\Database::connect();
            $passwordHash  = password_hash($tempPassword, PASSWORD_DEFAULT);
            $passwordField = $db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';

            $updateRow = [
                $passwordField => $passwordHash,
                'status' => 'active',
                'force_password_change' => 1,
                'activation_token' => null,
            ];
            if ($db->fieldExists('forgot_password_expires_at', 'users')) {
                $updateRow['forgot_password_expires_at'] = null;
            }
            if ($db->fieldExists('temp_password_source', 'users')) {
                $updateRow['temp_password_source'] = 'purchase';
            }
            $userModel->update($user['id'], $updateRow);
        }
>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162

        if ($db->fieldExists('force_password_change', 'users'))      $updateData['force_password_change']      = 1;
        if ($db->fieldExists('temp_password_source', 'users'))        $updateData['temp_password_source']       = 'purchase';
        if ($db->fieldExists('forgot_password_expires_at', 'users')) $updateData['forgot_password_expires_at'] = null;

        $userModel->update($user['id'], $updateData);
        $user = $userModel->find($user['id']);

        // Login URLs
        $mainLoginUrl   = base_url('user-login') . '?email=' . urlencode($email);
        $normalLoginUrl = base_url('user-login');

        // Payment success email
        if (is_array($paymentContext) && $paymentContext !== []) {
            try {
                $txn       = new TransactionalEmailService();
                $payResult = $txn->sendPaymentSuccess(
                    $email,
                    (string) ($user['name'] ?? ''),
                    $paymentContext
                );
                if (!$payResult['sent']) {
                    log_message('error', 'Payment success email failed: ' . ($payResult['error'] ?? 'unknown'));
                }
            } catch (\Throwable $e) {
                log_message('error', 'Payment success email exception: ' . $e->getMessage());
            }
        }

        // Activation credentials email
        $mailResult = $this->sendActivationCredentialsEmail($email, $tempPassword, $mainLoginUrl, $normalLoginUrl);
        $emailSent  = $mailResult['sent'];

        $payload = [
            'email_sent'       => $emailSent,
            'temp_login_url'   => $mainLoginUrl,
            'normal_login_url' => $normalLoginUrl,
            'login_url'        => $mainLoginUrl,
        ];

        if (!$emailSent) {
            $payload['temp_password'] = $tempPassword;
            $payload['email_error']   = $mailResult['error'] ?? 'email_failed';
        }

        // Certificate generate karo if course_name provided
        if (is_array($paymentContext) && !empty($paymentContext['course_name'])) {
            try {
                $certService = new \App\Services\CertificateService();
                $courseModel = new \App\Models\CourseModel();
                $courseName  = $paymentContext['course_name'];

                $course = $courseModel->where('title', $courseName)->first();

                if (!$course) {
                    $course = $courseModel->where('LOWER(title)', strtolower($courseName))->first();
                }
                if (!$course) {
                    $course = $courseModel->like('title', trim($courseName), 'both')->first();
                }
                if (!$course && !empty($paymentContext['course_id'])) {
                    $course = $courseModel->find((int) $paymentContext['course_id']);
                }
                if (!$course && strlen($courseName) > 3) {
                    foreach (array_filter(explode(' ', $courseName)) as $word) {
                        if (strlen($word) > 3) {
                            $course = $courseModel->like('title', $word, 'both')->first();
                            if ($course) break;
                        }
                    }
                }
                if (!$course) {
                    $allCourses = $courseModel->where('status', 'published')->findAll();
                    if (count($allCourses) === 1) {
                        $course = $allCourses[0];
                    }
                }

                if ($course) {
<<<<<<< HEAD
                    // Auto-enroll
                    $alreadyEnrolled = $db->table('enrollments')
                        ->where('user_id', (int) $user['id'])
                        ->where('course_id', (int) $course['id'])
                        ->countAllResults();

                    if (!$alreadyEnrolled) {
                        $db->table('enrollments')->insert([
                            'user_id'          => (int) $user['id'],
                            'course_id'        => (int) $course['id'],
                            'enrolled_at'      => date('Y-m-d H:i:s'),
                            'progress_percent' => 0,
                            'status'           => 'active',
                        ]);
                    }

                    $score       = (int) ($paymentContext['quiz_score'] ?? 0);
                    $total       = (int) ($paymentContext['quiz_total'] ?? 10);
=======
                    log_message('info', 'Course found for certificate: ' . $course['title'] . ' (requested: ' . $courseName . ')');
                    $score = (int) ($paymentContext['quiz_score'] ?? 0);
                    $total = (int) ($paymentContext['quiz_total'] ?? 10);

                    // Auto-enroll student in course after payment
                    try {
                        $db = \Config\Database::connect();
                        $alreadyEnrolled = $db->table('enrollments')
                            ->where('user_id', (int) $user['id'])
                            ->where('course_id', (int) $course['id'])
                            ->countAllResults();
                        if (!$alreadyEnrolled) {
                            $db->table('enrollments')->insert([
                                'user_id'          => (int) $user['id'],
                                'course_id'        => (int) $course['id'],
                                'enrolled_at'      => date('Y-m-d H:i:s'),
                                'progress_percent' => 0,
                                'status'           => 'active',
                            ]);
                            log_message('info', 'Auto-enrolled user=' . $user['id'] . ' in course=' . $course['id']);
                        }
                    } catch (\Throwable $e) {
                        log_message('error', 'Auto-enroll failed: ' . $e->getMessage());
                    }

>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
                    $certificate = $certService->generateIfNotExists((int) $user['id'], (int) $course['id'], $score, $total);

                    if ($certificate && is_array($certificate)) {
                        $payload['certificate_id']           = $certificate['id'] ?? null;
                        $payload['certificate_number']       = $certificate['certificate_number'] ?? null;
                        $payload['certificate_download_url'] = base_url('api/certificates/download/' . urlencode((string) ($certificate['certificate_number'] ?? $certificate['id'])));
                    }
                } else {
                    log_message('warning', 'Course not found for certificate: ' . $courseName);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Certificate generation failed (activation continues): ' . $e->getMessage());
            }
        }

        return ['ok' => true, 'payload' => $payload];
    }

    /**
     * @return array{sent: bool, error?: string}
     */
    private function sendActivationCredentialsEmail(
        string $toEmail,
        string $tempPassword,
        string $mainLoginUrl,
        string $normalLoginUrl
    ): array {
        $config = config('Email');
        $from   = $config->fromEmail ?? '';

        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Activation email skipped: set email.fromEmail in .env');
            return ['sent' => false, 'error' => 'email_from_not_configured'];
        }
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'error' => 'invalid_recipient'];
        }

        $fromName      = $config->fromName !== '' ? $config->fromName : 'Corso E-Learning';
        $safeEmail     = htmlspecialchars($toEmail,       ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safePass      = htmlspecialchars($tempPassword,  ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeMainUrl   = htmlspecialchars($mainLoginUrl,  ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeNormalUrl = htmlspecialchars($normalLoginUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $plain  = "Hello,\r\n\r\n";
        $plain .= "Thank you for your payment. Your Corso E-Learning account is ready.\r\n\r\n";
        $plain .= "SIGN IN:\r\n{$mainLoginUrl}\r\n\r\n";
        $plain .= "Email: {$toEmail}\r\nTemporary password: {$tempPassword}\r\n\r\n";
        $plain .= "After sign in, you will be asked to set a new password.\r\n\r\n";
        $plain .= "Future sign-ins:\r\n{$normalLoginUrl}\r\n\r\n— Corso E-Learning\r\n";

        $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,Segoe UI,sans-serif;line-height:1.55;color:#1a1a1a;">';
        $html .= '<p>Hello,</p>';
        $html .= '<p>Thank you for your payment. Your <strong>Corso E-Learning</strong> account is ready.</p>';
        $html .= '<h2 style="font-size:1rem;margin:24px 0 8px;">Sign in</h2>';
        $html .= '<p><a href="' . $safeMainUrl . '" style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;">Open sign in</a></p>';
        $html .= '<table style="margin:16px 0;border-collapse:collapse;">';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Email</td><td><strong>' . $safeEmail . '</strong></td></tr>';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Temporary password</td><td><strong>' . $safePass . '</strong></td></tr>';
        $html .= '</table>';
        $html .= '<p style="font-size:0.9rem;color:#555;">After sign in you will set a new password.</p>';
        $html .= '<h2 style="font-size:1rem;margin:24px 0 8px;">Future sign-ins</h2>';
        $html .= '<p><a href="' . $safeNormalUrl . '">' . $safeNormalUrl . '</a></p>';
        $html .= '<p style="margin-top:28px;font-size:0.85rem;color:#666;">— Corso E-Learning</p>';
        $html .= '</body></html>';

        $emailSvc = \Config\Services::email();
        $emailSvc->setFrom($from, $fromName);
        $emailSvc->setTo($toEmail);
        $emailSvc->setSubject('Your Corso account — login details');
        $emailSvc->setMailType('html');
        $emailSvc->setMessage($html);
        $emailSvc->setAltMessage($plain);

        try {
            if (!$emailSvc->send()) {
                log_message('error', 'Activation email send failed: ' . $emailSvc->printDebugger(['headers']));
                return ['sent' => false, 'error' => 'send_failed'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Activation email exception: ' . $e->getMessage());
            return ['sent' => false, 'error' => 'send_exception'];
        }

        return ['sent' => true];
    }
}