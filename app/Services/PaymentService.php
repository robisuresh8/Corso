<?php
namespace App\Services;

use App\Models\PaymentModel;
use App\Models\EnrollmentModel;

class PaymentService
{
    protected $paymentModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    // Initiate payment (store as pending)
    public function initiatePayment(array $data)
    {
        $data['status'] = 'pending';
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->paymentModel->insert($data);
    }

    // Mark payment as success
    public function completePayment($paymentId, $transactionId)
    {
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) return false;

        // Update payment
        $this->paymentModel->update($paymentId, [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'paid_at' => date('Y-m-d H:i:s')
        ]);

        // Update enrollment as active
        $this->enrollmentModel->update($payment['enrollment_id'], [
            'status' => 'active'
        ]);

        return true;
    }

    // Mark payment as failed
    public function failPayment($paymentId)
    {
        return $this->paymentModel->update($paymentId, [
            'status' => 'failed'
        ]);
    }

    // Get payments for a student
    public function getPaymentsByUser($userId)
    {
        return $this->paymentModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    // Get all payments (admin)
    public function getAllPayments()
    {
        return $this->paymentModel->orderBy('created_at', 'DESC')->findAll();
    }
}
