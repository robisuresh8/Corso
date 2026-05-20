<?php

namespace App\Services;

use Throwable;

/**
 * SMTP transactional mail (Brevo / Config\Email).
 */
class TransactionalEmailService
{
    /**
     * Welcome email sent after direct registration (api/auth/register).
     * Includes login credentials and email verification link.
     *
     * @return array{sent: bool, error?: string}
     */
    public function sendRegistrationWelcome(
        string $toEmail,
        string $name,
        string $plainPassword,
        string $verifyLink
    ): array {
        $safeName = htmlspecialchars($name !== '' ? $name : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safePass = htmlspecialchars($plainPassword, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeUrl  = htmlspecialchars($verifyLink, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeEmail = htmlspecialchars($toEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Your account is ready';

        $plain  = "Hello {$name},\r\n\r\n";
        $plain .= "Your Corso account has been created successfully.\r\n\r\n";
        $plain .= "Email:    {$toEmail}\r\n";
        $plain .= "Password: {$plainPassword}\r\n\r\n";
        $plain .= "Please verify your email address by clicking the link below:\r\n";
        $plain .= "{$verifyLink}\r\n\r\n";
        $plain .= "If you did not create this account, please ignore this email.\r\n\r\n";
        $plain .= "— Corso E-Learning\r\n";

        $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
        $html .= '<body style="font-family:system-ui,Segoe UI,sans-serif;line-height:1.6;color:#1a1a1a;max-width:520px;margin:0 auto;padding:24px;">';
        $html .= '<h2 style="color:#2563eb;margin-bottom:4px;">Welcome to Corso!</h2>';
        $html .= '<p>Hello ' . $safeName . ',</p>';
        $html .= '<p>Your account has been created successfully. Here are your login details:</p>';
        $html .= '<table style="margin:16px 0;border-collapse:collapse;background:#f8fafc;border-radius:8px;padding:12px;width:100%;">';
        $html .= '<tr><td style="padding:8px 16px 8px 12px;color:#555;width:110px;">Email</td>';
        $html .= '<td style="padding:8px 0;"><strong>' . $safeEmail . '</strong></td></tr>';
        $html .= '<tr><td style="padding:8px 16px 8px 12px;color:#555;">Password</td>';
        $html .= '<td style="padding:8px 0;"><strong>' . $safePass . '</strong></td></tr>';
        $html .= '</table>';
        $html .= '<p style="margin:20px 0;">Please verify your email to activate your account:</p>';
        $html .= '<p style="margin:20px 0;">';
        $html .= '<a href="' . $safeUrl . '" style="display:inline-block;padding:11px 24px;background:#2563eb;color:#fff;';
        $html .= 'text-decoration:none;border-radius:8px;font-weight:600;">Verify Email</a>';
        $html .= '</p>';
        $html .= '<p style="word-break:break-all;font-size:0.85rem;color:#666;">Or copy this link: <a href="' . $safeUrl . '">' . $safeUrl . '</a></p>';
        $html .= '<hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">';
        $html .= '<p style="font-size:0.85rem;color:#999;">If you did not create this account, you can safely ignore this email.</p>';
        $html .= '<p style="font-size:0.85rem;color:#666;">— Corso E-Learning</p>';
        $html .= '</body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    /**
     * @return array{sent: bool, error?: string}
     */
    public function sendPaymentSuccess(
        string $toEmail,
        string $recipientName,
        array $ctx
    ): array {
        $orderId = (string) ($ctx['order_id'] ?? '');
        $paymentId = (string) ($ctx['payment_id'] ?? '');
        $currency = (string) ($ctx['currency'] ?? 'INR');
        $amountPaise = (int) ($ctx['amount_paise'] ?? 0);
        $course = trim((string) ($ctx['course_name'] ?? ''));
        $paidAt = (string) ($ctx['paid_at'] ?? date('Y-m-d H:i:s'));

        $amountMajor = $amountPaise > 0 ? number_format($amountPaise / 100, 2) : '—';
        $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Payment received';
        $plain = "Hello {$recipientName},\r\n\r\n";
        $plain .= "We received your payment successfully.\r\n\r\n";
        $plain .= "Razorpay order ID: {$orderId}\r\n";
        $plain .= "Razorpay payment ID: {$paymentId}\r\n";
        $plain .= "Amount: {$currency} {$amountMajor}\r\n";
        $plain .= "Time: {$paidAt}\r\n";
        if ($course !== '') {
            $plain .= "Course / assessment: {$course}\r\n";
        }
        $plain .= "\r\nYou will receive a separate email with your account login instructions.\r\n\r\n— Corso E-Learning\r\n";

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,Segoe UI,sans-serif;line-height:1.55;color:#1a1a1a;">';
        $html .= '<p>Hello ' . $safeName . ',</p>';
        $html .= '<p>We received your <strong>payment successfully</strong>.</p>';
        $html .= '<table style="margin:16px 0;border-collapse:collapse;">';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Razorpay order ID</td><td><strong>' . htmlspecialchars($orderId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Razorpay payment ID</td><td><strong>' . htmlspecialchars($paymentId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Amount</td><td><strong>' . htmlspecialchars($currency . ' ' . $amountMajor, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Time</td><td>' . htmlspecialchars($paidAt, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>';
        if ($course !== '') {
            $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Course / assessment</td><td>' . htmlspecialchars($course, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '<p style="font-size:0.95rem;color:#444;">You will receive a separate email with your account login instructions.</p>';
        $html .= '<p style="margin-top:28px;font-size:0.85rem;color:#666;">— Corso E-Learning</p>';
        $html .= '</body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    /**
     * @return array{sent: bool, error?: string}
     */
    public function sendPaymentFailure(
        string $toEmail,
        string $recipientName,
        array $ctx
    ): array {
        $orderId = (string) ($ctx['order_id'] ?? '');
        $paymentId = (string) ($ctx['payment_id'] ?? '');
        $currency = (string) ($ctx['currency'] ?? 'INR');
        $amountPaise = (int) ($ctx['amount_paise'] ?? 0);
        $course = trim((string) ($ctx['course_name'] ?? ''));
        $reason = trim((string) ($ctx['error_description'] ?? 'Payment could not be completed'));
        $errorCode = trim((string) ($ctx['error_code'] ?? ''));

        $amountMajor = $amountPaise > 0 ? number_format($amountPaise / 100, 2) : '—';
        $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Payment was not completed';
        $plain = "Hello {$recipientName},\r\n\r\n";
        $plain .= "Your payment was not completed.\r\n\r\n";
        if ($orderId !== '') {
            $plain .= "Razorpay order ID: {$orderId}\r\n";
        }
        if ($paymentId !== '') {
            $plain .= "Razorpay payment ID: {$paymentId}\r\n";
        }
        $plain .= "Attempted amount: {$currency} {$amountMajor}\r\n";
        if ($errorCode !== '') {
            $plain .= "Error code: {$errorCode}\r\n";
        }
        $plain .= "Details: {$reason}\r\n";
        if ($course !== '') {
            $plain .= "Course / assessment: {$course}\r\n";
        }
        $plain .= "\r\nYou can try again from the skill check flow. If money was debited, Razorpay or your bank will reverse it per their policy.\r\n\r\n— Corso E-Learning\r\n";

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,Segoe UI,sans-serif;line-height:1.55;color:#1a1a1a;">';
        $html .= '<p>Hello ' . $safeName . ',</p>';
        $html .= '<p>Your <strong>payment was not completed</strong>.</p>';
        $html .= '<table style="margin:16px 0;border-collapse:collapse;">';
        if ($orderId !== '') {
            $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Razorpay order ID</td><td><strong>' . htmlspecialchars($orderId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        }
        if ($paymentId !== '') {
            $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Razorpay payment ID</td><td><strong>' . htmlspecialchars($paymentId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        }
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Attempted amount</td><td><strong>' . htmlspecialchars($currency . ' ' . $amountMajor, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        if ($errorCode !== '') {
            $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Error code</td><td>' . htmlspecialchars($errorCode, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>';
        }
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;vertical-align:top;">Details</td><td>' . htmlspecialchars($reason, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>';
        if ($course !== '') {
            $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Course / assessment</td><td>' . htmlspecialchars($course, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '<p style="font-size:0.95rem;color:#444;">You can try again from the skill check flow. If money was debited, your bank or Razorpay will handle reversal per their rules.</p>';
        $html .= '<p style="margin-top:28px;font-size:0.85rem;color:#666;">— Corso E-Learning</p>';
        $html .= '</body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    /**
     * Forgot password: user must sign in on main login page, then set a new password.
     *
     * @return array{sent: bool, error?: string}
     */
    public function sendForgotPasswordTemp(
        string $toEmail,
        string $recipientName,
        string $plainPassword,
        string $mainLoginUrl,
        string $expiresAtHuman
    ): array {
        $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safePass = htmlspecialchars($plainPassword, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeUrl = htmlspecialchars($mainLoginUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Temporary password (reset request)';
        $plain = "Hello {$recipientName},\r\n\r\n";
        $plain .= "You requested a new temporary password.\r\n\r\n";
        $plain .= "Sign in here (main login — not the first-time purchase page):\r\n{$mainLoginUrl}\r\n\r\n";
        $plain .= "Email: {$toEmail}\r\n";
        $plain .= "Temporary password: {$plainPassword}\r\n\r\n";
        $plain .= "After you sign in, you will be asked to choose a new password. ";
        $plain .= "This temporary password stops working after you set a new one, or after {$expiresAtHuman} if you do not use it.\r\n\r\n";
        $plain .= "If you did not request this, ignore this email.\r\n\r\n— Corso E-Learning\r\n";

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,Segoe UI,sans-serif;line-height:1.55;color:#1a1a1a;">';
        $html .= '<p>Hello ' . $safeName . ',</p>';
        $html .= '<p>You requested a new <strong>temporary password</strong>.</p>';
        $html .= '<p>Sign in on the <strong>main login page</strong> (not the first-time purchase link):</p>';
        $html .= '<p style="margin:16px 0;"><a href="' . $safeUrl . '" style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;">Open sign in</a></p>';
        $html .= '<p style="word-break:break-all;font-size:0.9rem;"><a href="' . $safeUrl . '">' . $safeUrl . '</a></p>';
        $html .= '<table style="margin:16px 0;border-collapse:collapse;">';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Email</td><td><strong>' . htmlspecialchars($toEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>';
        $html .= '<tr><td style="padding:4px 12px 4px 0;color:#555;">Temporary password</td><td><strong>' . $safePass . '</strong></td></tr>';
        $html .= '</table>';
        $html .= '<p style="font-size:0.95rem;color:#444;">After you sign in, choose a new password when prompted. ';
        $html .= 'This temporary password stops working after you set a new one, or after <strong>' . htmlspecialchars($expiresAtHuman, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong> if unused.</p>';
        $html .= '<p style="margin-top:28px;font-size:0.85rem;color:#666;">— Corso E-Learning</p>';
        $html .= '</body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    /**
     * Forgot password: single-use link to choose a new password (no temporary password).
     *
     * @return array{sent: bool, error?: string}
     */
    public function sendPasswordResetLink(
        string $toEmail,
        string $recipientName,
        string $resetPageUrl,
        string $expiresAtHuman
    ): array {
        $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeUrl = htmlspecialchars($resetPageUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Reset your password';
        $plain = "Hello {$recipientName},\r\n\r\n";
        $plain .= "We received a request to reset the password for {$toEmail}.\r\n\r\n";
        $plain .= "Open this link to choose a new password:\r\n{$resetPageUrl}\r\n\r\n";
        $plain .= "The link expires after {$expiresAtHuman}. It can only be used once.\r\n\r\n";
        $plain .= "If you did not request this, you can ignore this email.\r\n\r\n— Corso E-Learning\r\n";

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,Segoe UI,sans-serif;line-height:1.55;color:#1a1a1a;">';
        $html .= '<p>Hello ' . $safeName . ',</p>';
        $html .= '<p>We received a request to reset the password for <strong>' . htmlspecialchars($toEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong>.</p>';
        $html .= '<p style="margin:16px 0;"><a href="' . $safeUrl . '" style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;">Set new password</a></p>';
        $html .= '<p style="word-break:break-all;font-size:0.9rem;"><a href="' . $safeUrl . '">' . $safeUrl . '</a></p>';
        $html .= '<p style="font-size:0.95rem;color:#444;">This link expires after <strong>' . htmlspecialchars($expiresAtHuman, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong> and can only be used once.</p>';
        $html .= '<p style="font-size:0.95rem;color:#444;">If you did not request a password reset, you can ignore this email.</p>';
        $html .= '<p style="margin-top:28px;font-size:0.85rem;color:#666;">— Corso E-Learning</p>';
        $html .= '</body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    /**
     * @return array{sent: bool, error?: string}
     */
    private function deliver(string $toEmail, string $subject, string $html, string $plain): array
    {
        $config = config('Email');
        $from = $config->fromEmail ?? '';
        if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Transactional email skipped: set email.fromEmail in .env');

            return ['sent' => false, 'error' => 'email_from_not_configured'];
        }
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'error' => 'invalid_recipient'];
        }

        $fromName = $config->fromName !== '' ? $config->fromName : 'Corso E-Learning';

        $email = \Config\Services::email();
        $email->setFrom($from, $fromName);
        $email->setTo($toEmail);
        $email->setSubject($subject);
        $email->setMailType('html');
        $email->setMessage($html);
        $email->setAltMessage($plain);

        try {
            if (!$email->send()) {
                log_message('error', 'Transactional email send failed: ' . $email->printDebugger(['headers']));

                return ['sent' => false, 'error' => 'send_failed'];
            }
        } catch (Throwable $e) {
            log_message('error', 'Transactional email exception: ' . $e->getMessage());

            return ['sent' => false, 'error' => 'send_exception'];
        }

        log_message('info', 'Transactional email SMTP accepted: from=' . $from . ' to=' . $toEmail);

        return ['sent' => true];
    }
}