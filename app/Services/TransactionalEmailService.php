<?php

namespace App\Services;

use Throwable;

/**
 * Transactional mail via Brevo API (no SMTP needed).
 */
class TransactionalEmailService
{
    public function sendRegistrationWelcome(
        string $toEmail,
        string $name,
        string $plainPassword,
        string $verifyLink
    ): array {
        $safeName  = htmlspecialchars($name !== '' ? $name : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safePass  = htmlspecialchars($plainPassword, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeUrl   = htmlspecialchars($verifyLink, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeEmail = htmlspecialchars($toEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Your account is ready';
        $plain   = "Hello {$name},\r\n\r\nYour Corso account has been created.\r\n\nEmail: {$toEmail}\r\nPassword: {$plainPassword}\r\n\r\nVerify: {$verifyLink}\r\n\r\n— Corso E-Learning\r\n";
        $html    = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,sans-serif;line-height:1.6;color:#1a1a1a;max-width:520px;margin:0 auto;padding:24px;">'
            . '<h2 style="color:#2563eb;">Welcome to Corso!</h2>'
            . '<p>Hello ' . $safeName . ',</p>'
            . '<p>Your account is ready:</p>'
            . '<table style="margin:16px 0;background:#f8fafc;padding:12px;width:100%;"><tr><td style="padding:8px 16px 8px 12px;color:#555;width:110px;">Email</td><td><strong>' . $safeEmail . '</strong></td></tr>'
            . '<tr><td style="padding:8px 16px 8px 12px;color:#555;">Password</td><td><strong>' . $safePass . '</strong></td></tr></table>'
            . '<p><a href="' . $safeUrl . '" style="display:inline-block;padding:11px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Verify Email</a></p>'
            . '<p style="font-size:0.85rem;color:#666;">— Corso E-Learning</p></body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    public function sendPaymentSuccess(string $toEmail, string $recipientName, array $ctx): array
    {
        $orderId     = (string) ($ctx['order_id']    ?? '');
        $paymentId   = (string) ($ctx['payment_id']  ?? '');
        $currency    = (string) ($ctx['currency']    ?? 'INR');
        $amountPaise = (int)    ($ctx['amount_paise'] ?? 0);
        $course      = trim((string) ($ctx['course_name'] ?? ''));
        $paidAt      = (string) ($ctx['paid_at']     ?? date('Y-m-d H:i:s'));
        $amountMajor = $amountPaise > 0 ? number_format($amountPaise / 100, 2) : '—';
        $safeName    = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Payment received';
        $plain   = "Hello {$recipientName},\r\n\r\nPayment received.\r\nOrder ID: {$orderId}\r\nPayment ID: {$paymentId}\r\nAmount: {$currency} {$amountMajor}\r\nTime: {$paidAt}\r\n" . ($course ? "Course: {$course}\r\n" : '') . "\r\n— Corso E-Learning\r\n";
        $html    = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,sans-serif;line-height:1.55;color:#1a1a1a;">'
            . '<p>Hello ' . $safeName . ',</p><p>Payment received successfully.</p>'
            . '<table style="margin:16px 0;border-collapse:collapse;">'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Order ID</td><td><strong>' . htmlspecialchars($orderId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Payment ID</td><td><strong>' . htmlspecialchars($paymentId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Amount</td><td><strong>' . htmlspecialchars($currency . ' ' . $amountMajor, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Time</td><td>' . htmlspecialchars($paidAt, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>'
            . ($course ? '<tr><td style="padding:4px 12px 4px 0;color:#555;">Course</td><td>' . htmlspecialchars($course, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>' : '')
            . '</table><p style="font-size:0.85rem;color:#666;">— Corso E-Learning</p></body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    public function sendPaymentFailure(string $toEmail, string $recipientName, array $ctx): array
    {
        $orderId     = (string) ($ctx['order_id']          ?? '');
        $currency    = (string) ($ctx['currency']          ?? 'INR');
        $amountPaise = (int)    ($ctx['amount_paise']      ?? 0);
        $course      = trim((string) ($ctx['course_name']  ?? ''));
        $reason      = trim((string) ($ctx['error_description'] ?? 'Payment could not be completed'));
        $amountMajor = $amountPaise > 0 ? number_format($amountPaise / 100, 2) : '—';
        $safeName    = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Payment was not completed';
        $plain   = "Hello {$recipientName},\r\n\r\nYour payment was not completed.\r\nAmount: {$currency} {$amountMajor}\r\nReason: {$reason}\r\n\r\nPlease try again.\r\n\r\n— Corso E-Learning\r\n";
        $html    = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,sans-serif;line-height:1.55;color:#1a1a1a;">'
            . '<p>Hello ' . $safeName . ',</p><p>Your payment was <strong>not completed</strong>.</p>'
            . '<table style="margin:16px 0;border-collapse:collapse;">'
            . ($orderId ? '<tr><td style="padding:4px 12px 4px 0;color:#555;">Order ID</td><td><strong>' . htmlspecialchars($orderId, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>' : '')
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Amount</td><td><strong>' . htmlspecialchars($currency . ' ' . $amountMajor, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Reason</td><td>' . htmlspecialchars($reason, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>'
            . ($course ? '<tr><td style="padding:4px 12px 4px 0;color:#555;">Course</td><td>' . htmlspecialchars($course, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</td></tr>' : '')
            . '</table><p style="font-size:0.85rem;color:#666;">— Corso E-Learning</p></body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    public function sendForgotPasswordTemp(
        string $toEmail,
        string $recipientName,
        string $plainPassword,
        string $mainLoginUrl,
        string $expiresAtHuman
    ): array {
        $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safePass = htmlspecialchars($plainPassword, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeUrl  = htmlspecialchars($mainLoginUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Temporary password';
        $plain   = "Hello {$recipientName},\r\n\r\nYour temporary password:\r\nEmail: {$toEmail}\r\nPassword: {$plainPassword}\r\n\r\nSign in: {$mainLoginUrl}\r\nExpires: {$expiresAtHuman}\r\n\r\n— Corso E-Learning\r\n";
        $html    = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,sans-serif;line-height:1.55;color:#1a1a1a;">'
            . '<p>Hello ' . $safeName . ',</p><p>Your temporary password:</p>'
            . '<table style="margin:16px 0;border-collapse:collapse;">'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Email</td><td><strong>' . htmlspecialchars($toEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong></td></tr>'
            . '<tr><td style="padding:4px 12px 4px 0;color:#555;">Password</td><td><strong>' . $safePass . '</strong></td></tr>'
            . '</table>'
            . '<p><a href="' . $safeUrl . '" style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;">Sign in</a></p>'
            . '<p style="font-size:0.9rem;color:#555;">Expires: ' . htmlspecialchars($expiresAtHuman, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>'
            . '<p style="font-size:0.85rem;color:#666;">— Corso E-Learning</p></body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    public function sendPasswordResetLink(
        string $toEmail,
        string $recipientName,
        string $resetPageUrl,
        string $expiresAtHuman
    ): array {
        $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'there', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeUrl  = htmlspecialchars($resetPageUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $subject = 'Corso — Reset your password';
        $plain   = "Hello {$recipientName},\r\n\r\nReset your password:\r\n{$resetPageUrl}\r\n\r\nExpires: {$expiresAtHuman}\r\n\r\n— Corso E-Learning\r\n";
        $html    = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:system-ui,sans-serif;line-height:1.55;color:#1a1a1a;">'
            . '<p>Hello ' . $safeName . ',</p>'
            . '<p>Reset your password for <strong>' . htmlspecialchars($toEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong>:</p>'
            . '<p><a href="' . $safeUrl . '" style="display:inline-block;padding:10px 18px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;">Set new password</a></p>'
            . '<p style="font-size:0.9rem;color:#555;">Expires: ' . htmlspecialchars($expiresAtHuman, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '. One-time use only.</p>'
            . '<p style="font-size:0.85rem;color:#666;">— Corso E-Learning</p></body></html>';

        return $this->deliver($toEmail, $subject, $html, $plain);
    }

    /**
     * Send via Brevo Transactional Email API (no SMTP needed).
     * Reads BREVO_API_KEY from env.
     *
     * @return array{sent: bool, error?: string}
     */
    private function deliver(string $toEmail, string $subject, string $html, string $plain): array
    {
        // Sender email
        $fromEmail = env('email.fromEmail') ?: getenv('email.fromEmail') ?: env('EMAIL_FROM_EMAIL') ?: getenv('EMAIL_FROM_EMAIL') ?: '';
        $fromName  = env('email.fromName') ?: getenv('email.fromName') ?: env('EMAIL_FROM_NAME') ?: getenv('EMAIL_FROM_NAME') ?: 'Corso E-Learning';
        $apiKey    = env('BREVO_API_KEY')   ?: getenv('BREVO_API_KEY')   ?: '';

        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Brevo email skipped: set email.fromEmail in env');
            return ['sent' => false, 'error' => 'email_from_not_configured'];
        }
        if ($apiKey === '') {
            log_message('error', 'Brevo email skipped: set BREVO_API_KEY in env');
            return ['sent' => false, 'error' => 'brevo_api_key_not_configured'];
        }
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'error' => 'invalid_recipient'];
        }

        $payload = json_encode([
            'sender'      => ['name' => $fromName, 'email' => $fromEmail],
            'to'          => [['email' => $toEmail]],
            'subject'     => $subject,
            'htmlContent' => $html,
            'textContent' => $plain,
        ], JSON_THROW_ON_ERROR);

        try {
            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    'accept: application/json',
                    'api-key: ' . $apiKey,
                    'content-type: application/json',
                ],
                CURLOPT_TIMEOUT        => 15,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                log_message('error', 'Brevo curl error: ' . $curlErr);
                return ['sent' => false, 'error' => 'curl_error'];
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                log_message('error', 'Brevo API error ' . $httpCode . ': ' . $response);
                return ['sent' => false, 'error' => 'brevo_api_error_' . $httpCode];
            }

            log_message('info', 'Brevo email sent to=' . $toEmail . ' subject=' . $subject);
            return ['sent' => true];

        } catch (Throwable $e) {
            log_message('error', 'Brevo email exception: ' . $e->getMessage());
            return ['sent' => false, 'error' => 'exception'];
        }
    }
}