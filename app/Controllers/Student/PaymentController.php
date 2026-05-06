<?php
namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\PaymentService;
use App\Services\EnrollmentService;

class PaymentController extends BaseController
{
    protected $paymentService;
    protected $enrollmentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->enrollmentService = new EnrollmentService();
    }

    // Initiate payment
    public function pay($courseId)
    {
        $userId = session()->get('user_id');

        // Check enrollment
        $enrollment = $this->enrollmentService->isEnrolled($userId, $courseId);
        if (!$enrollment) {
            $enrollmentId = $this->enrollmentService->enroll([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'pending'
            ]);
        } else {
            $enrollmentId = $enrollment['id'];
        }

        // Initiate payment
        $amount = 100.00; // replace with actual course price
        $paymentId = $this->paymentService->initiatePayment([
            'user_id' => $userId,
            'course_id' => $courseId,
            'enrollment_id' => $enrollmentId,
            'amount' => $amount,
            'payment_method' => 'stripe'
        ]);

        // Redirect to payment gateway (simulated)
        return redirect()->to("/student/payment/callback/$paymentId/success");
    }

    // Payment callback
    public function callback($paymentId, $status)
    {
        if ($status === 'success') {
            $transactionId = 'TXN' . time();
            $this->paymentService->completePayment($paymentId, $transactionId);
            return redirect()->to('/student/courses')->with('message', 'Payment successful!');
        } else {
            $this->paymentService->failPayment($paymentId);
            return redirect()->back()->with('error', 'Payment failed.');
        }
    }

    // Student payment history
    public function myPayments()
    {
        $userId = session()->get('user_id');
        $payments = $this->paymentService->getPaymentsByUser($userId);
        return view('student/payment/index', ['payments' => $payments]);
    }
}
