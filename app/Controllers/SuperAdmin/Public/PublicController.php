<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\CourseService;

class PublicController extends BaseController
{
    protected CourseService $courseService;

    public function __construct()
    {
        $this->courseService = new CourseService();
    }

    // GET /public/courses
    // All published courses — no login required
    public function courses()
    {
        try {
            $courses = $this->courseService->getPublishedCourses();

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $courses,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Public courses error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Something went wrong',
            ]);
        }
    }

    // GET /public/courses/{id}
    // Single course detail — no login required
    public function courseDetail($id)
    {
        try {
            $course = $this->courseService->getCourseById($id);

            if (!$course || ($course['status'] ?? '') !== 'published') {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Course not found',
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [
                    'course'  => $course,
                    'actions' => [
                        'buy_now' => true,
                        'message' => 'Click Buy Now to take the quiz first',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', "Public course detail error | id:{$id} " . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Something went wrong',
            ]);
        }
    }
}