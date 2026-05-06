<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\CourseService;

class CourseController extends BaseController
{
    protected $courseService;

    public function __construct()
    {
        $this->courseService = new CourseService();
    }

    // Show only published courses
    public function index()
    {
        $data['courses'] = $this->courseService->getPublishedCourses();
        return view('student/course/index', $data);
    }

    // View course details
    public function show($id)
    {
        $course = $this->courseService->getCourseById($id);

        if (!$course || $course['status'] !== 'published') {
            return redirect()->to('/student/courses')
                ->with('error', 'Course not available.');
        }

        return view('student/course/show', ['course' => $course]);
    }

    // View my enrolled courses (optional)
    public function myCourses()
    {
        $userId = session()->get('user_id');

        // You need EnrollmentService for proper implementation
        $data['courses'] = $this->courseService->getEnrolledCourses($userId);

        return view('student/course/my_courses', $data);
    }
}
