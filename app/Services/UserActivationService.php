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

        $userModel    = new UserModel();
        $visitorModel = new \App\Models\VisitorModel();

        // Pehle visitors table mein dhundo
        $visitor = $visitorModel
            ->where('email', $email)
            ->where('cookie_token', $activationToken)
            ->first();

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

        // Activation credentials email — Brevo API ke through
        $txnMail   = new TransactionalEmailService();
        $mailResult = $txnMail->sendActivationCredentials($email, $tempPassword, $mainLoginUrl, $normalLoginUrl);
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
                if (!$course) $course = $courseModel->where('LOWER(title)', strtolower($courseName))->first();
                if (!$course) $course = $courseModel->like('title', trim($courseName), 'both')->first();
                if (!$course && !empty($paymentContext['course_id'])) $course = $courseModel->find((int) $paymentContext['course_id']);
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
                    if (count($allCourses) === 1) $course = $allCourses[0];
                }

                if ($course) {
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
                    }

                    $score       = (int) ($paymentContext['quiz_score'] ?? 0);
                    $total       = (int) ($paymentContext['quiz_total'] ?? 10);
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
                log_message('error', 'Certificate generation failed: ' . $e->getMessage());
            }
        }

        return ['ok' => true, 'payload' => $payload];
    }
}