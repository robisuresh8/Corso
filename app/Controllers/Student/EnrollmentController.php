<?php
namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\EnrollmentService;

class EnrollmentController extends BaseController
{
    protected $enrollmentService;

    public function __construct()
    {
        $this->enrollmentService = new EnrollmentService();
    }

    // Enroll in a course
    public function enroll($courseId)
    {
        $userId = session()->get('user_id'); // Logged-in student

        if ($this->enrollmentService->isEnrolled($userId, $courseId)) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }

        $this->enrollmentService->enroll([
            'user_id' => $userId,
            'course_id' => $courseId
        ]);

        return redirect()->to('/student/courses')->with('message', 'Enrolled successfully!');
    }

    // List all courses the student is enrolled in
    public function myCourses()
    {
        $userId = session()->get('user_id');
        $enrollments = $this->enrollmentService->getEnrollmentsByUser($userId);
        return view('student/enrollment/index', ['enrollments' => $enrollments]);
    }

    // Update progress (e.g., after quiz completion)
    public function updateProgress($courseId, $progress)
    {
        $userId = session()->get('user_id');
        $this->enrollmentService->updateProgress($userId, $courseId, $progress);
        return redirect()->back()->with('message', 'Progress updated!');
    }
}
