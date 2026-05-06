<?php
namespace App\Services;

use App\Models\EnrollmentModel;

class EnrollmentService
{
    protected $enrollmentModel;

    public function __construct()
    {
        $this->enrollmentModel = new EnrollmentModel();
    }

    // Check if a student is already enrolled
    public function isEnrolled($userId, $courseId)
    {
        return $this->enrollmentModel
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }

    // Enroll a student in a course
    public function enroll(array $data)
    {
        $existing = $this->isEnrolled($data['user_id'], $data['course_id']);
        if ($existing) {
            return false; // Already enrolled
        }

        $data['status'] = 'active';
        $data['enrolled_at'] = $data['enrolled_at'] ?? date('Y-m-d H:i:s');
        $data['progress_percent'] = $data['progress_percent'] ?? 0.00;

        return $this->enrollmentModel->insert($data);
    }

    // Get all enrollments for a user
    public function getEnrollmentsByUser($userId)
    {
        return $this->enrollmentModel
            ->where('user_id', $userId)
            ->orderBy('enrolled_at', 'DESC')
            ->findAll();
    }

    // Update course progress
    public function updateProgress($userId, $courseId, $progress)
    {
        $enrollment = $this->isEnrolled($userId, $courseId);
        if (!$enrollment) return false;

        $status = $progress >= 100 ? 'completed' : $enrollment['status'];

        return $this->enrollmentModel->update($enrollment['id'], [
            'progress_percent' => $progress,
            'status' => $status
        ]);
    }

    // Cancel enrollment
    public function cancelEnrollment($userId, $courseId)
    {
        $enrollment = $this->isEnrolled($userId, $courseId);
        if (!$enrollment) return false;

        return $this->enrollmentModel->update($enrollment['id'], [
            'status' => 'cancelled'
        ]);
    }
}
