<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\UserActivationService;
use App\Services\TransactionalEmailService;
use Config\Razorpay;

class RazorpayController extends BaseController
{
    /**
     * POST /api/payments/razorpay/create-order
     * Creates a Razorpay order (amount from config).
     */
    public function createOrder()
    {
        /** @var Razorpay $cfg */
        $cfg = config('Razorpay');

        if ($cfg->keyId === '' || $cfg->keySecret === '') {
            return $this->response
                ->setStatusCode(503)
                ->setJSON(['error' => 'Razorpay is not configured (set razorpay.keyId and razorpay.keySecret in .env)']);
        }

        $amount = $cfg->amountPaise > 0 ? $cfg->amountPaise : 10000;

        $client = \Config\Services::curlrequest();
        try {
            $response = $client->post('https://api.razorpay.com/v1/orders', [
                'auth' => [$cfg->keyId, $cfg->keySecret],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
                'body' => json_encode([
                    'amount' => $amount,
                    'currency' => 'INR',
                    'receipt' => 'rcpt_' . bin2hex(random_bytes(8)),
                ], JSON_THROW_ON_ERROR),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Razorpay create order: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(502)
                ->setJSON(['error' => 'Could not reach Razorpay']);
        }

        $status = $response->getStatusCode();
        $raw = $response->getBody();
        $body = json_decode($raw, true);

        if (($status !== 200 && $status !== 201) || !is_array($body) || empty($body['id'])) {
            log_message('error', 'Razorpay order failed: ' . $raw);

            return $this->response
                ->setStatusCode(502)
                ->setJSON(['error' => 'Razorpay order failed', 'detail' => is_array($body) ? ($body['error']['description'] ?? $body) : $raw]);
        }

        return $this->response->setJSON([
            'key_id' => $cfg->keyId,
            'order_id' => $body['id'],
            'amount' => $amount,
            'currency' => 'INR',
        ]);
    }

    /**
     * POST /api/payments/razorpay/verify
     * Verifies payment signature and activates the user account.
     */
    public function debug()
{
    return $this->response->setJSON([
        'host_test' => getenv('EMAIL_SMTP_HOST') ?: 'NOT_FOUND',
        'user_test' => getenv('EMAIL_SMTP_USER') ?: 'NOT_FOUND',
        'pass_test' => getenv('EMAIL_SMTP_PASS') ? 'FOUND' : 'NOT_FOUND',
        'from_test' => getenv('EMAIL_FROM_EMAIL') ?: 'NOT_FOUND',
        'all_env_keys' => array_keys($_ENV),
    ]);
}

public function emailTest()
{
    $cfg = config('Email');

    $email = \Config\Services::email();

    $email->setTo('robisuresh0112@gmail.com');
    $email->setSubject('SMTP Test');
    $email->setMessage('SMTP working from Render');

    $sent = $email->send(false);

    return $this->response->setJSON([
        'sent'   => $sent,
        'config' => [
            'from'   => $cfg->fromEmail,
            'host'   => $cfg->SMTPHost,
            'user'   => $cfg->SMTPUser,
            'pass'   => $cfg->SMTPPass ? 'FOUND' : 'MISSING',
            'port'   => $cfg->SMTPPort,
            'crypto' => $cfg->SMTPCrypto,
            'protocol' => $cfg->protocol,
        ],
        'debug' => $email->printDebugger(['headers', 'subject']),
    ]);
}

public function verify()
    {
        /** @var Razorpay $cfg */
        $cfg = config('Razorpay');

        if ($cfg->keySecret === '') {
            return $this->response
                ->setStatusCode(503)
                ->setJSON(['error' => 'Razorpay is not configured']);
        }

        $data = $this->request->getJSON(true) ?? [];

        $orderId = trim((string) ($data['razorpay_order_id'] ?? ''));
        $paymentId = trim((string) ($data['razorpay_payment_id'] ?? ''));
        $signature = trim((string) ($data['razorpay_signature'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $activationToken = trim((string) ($data['activation_token'] ?? ''));
        $courseName = trim((string) ($data['course_name'] ?? ''));
        $quizScore = (int) ($data['quiz_score'] ?? 0);
        $quizTotal = (int) ($data['quiz_total'] ?? 10);

        if ($orderId === '' || $paymentId === '' || $signature === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'razorpay_order_id, razorpay_payment_id, and razorpay_signature are required']);
        }
        if ($email === '' || $activationToken === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'email and activation_token are required']);
        }

        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $cfg->keySecret);
        if (!hash_equals($expected, $signature)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Invalid payment signature']);
        }

        $amountPaise = $cfg->amountPaise > 0 ? $cfg->amountPaise : 10000;
        $paymentContext = [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'amount_paise' => $amountPaise,
            'currency' => 'INR',
            'paid_at' => date('Y-m-d H:i:s'),
            'course_name' => $courseName,
            'quiz_score' => $quizScore,
            'quiz_total' => $quizTotal,
        ];

        try {
            $activation = new UserActivationService();
            $result = $activation->activateByToken($email, $activationToken, $paymentContext);

            if (!$result['ok']) {
                log_message('error', 'Activation failed for email=' . $email . ': ' . $result['error']);
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => $result['error'], 'debug' => 'Check logs for details']);
            }

            return $this->response->setJSON($result['payload']);
        } catch (\Throwable $e) {
            log_message('error', 'Razorpay verify exception: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Internal error during activation. Please contact support.', 'debug' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/payments/razorpay/payment-failed
     * Client calls after Razorpay payment.failed. Emails inactive (pre-payment) users: match by email+token when token is sent, else by email alone.
     */
    public function paymentFailed()
    {
        $data = $this->request->getJSON(true) ?? [];

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $activationToken = trim((string) ($data['activation_token'] ?? ''));
        $orderId = trim((string) ($data['razorpay_order_id'] ?? ''));
        $paymentId = trim((string) ($data['razorpay_payment_id'] ?? ''));
        $courseName = trim((string) ($data['course_name'] ?? ''));
        $errorCode = trim((string) ($data['error_code'] ?? ''));
        $errorDescription = trim((string) ($data['error_description'] ?? 'Payment could not be completed'));

        $generic = ['ok' => true];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON($generic);
        }

        $userModel = new \App\Models\UserModel();

        $user = null;
        if ($activationToken !== '') {
            $user = $userModel
                ->where('email', $email)
                ->where('activation_token', $activationToken)
                ->where('status', 'inactive')
                ->first();
        }
        if (!$user) {
            $user = $userModel
                ->where('email', $email)
                ->where('status', 'inactive')
                ->first();
        }

        if (!$user) {
            return $this->response->setJSON($generic);
        }

        /** @var Razorpay $cfg */
        $cfg = config('Razorpay');
        $amountPaise = $cfg->amountPaise > 0 ? $cfg->amountPaise : 10000;

        $txn = new TransactionalEmailService();
        $txn->sendPaymentFailure(
            $email,
            (string) ($user['name'] ?? ''),
            [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'amount_paise' => $amountPaise,
                'currency' => 'INR',
                'course_name' => $courseName,
                'error_code' => $errorCode,
                'error_description' => $errorDescription,
            ]
        );

        return $this->response->setJSON($generic);
    }
}
