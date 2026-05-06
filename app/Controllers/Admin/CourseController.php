<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CourseService;

class CourseController extends BaseController
{
    protected $courseService;

    public function __construct()
    {
        $this->courseService = new CourseService();
    }

    // GET all courses
    public function index()
    {
        $courses = $this->courseService->getAllCourses();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $courses
        ]);
    }

    // GET single course for editing
    public function edit($id)
    {
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Course not found'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $course
        ]);
    }

    // POST store new course
    public function store()
    {
        $data = $this->request->getJSON(true);

        // Validation
        if (empty($data['title'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Title is required'
            ]);
        }

        if (empty($data['category_id'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Category is required'
            ]);
        }

        if (!in_array($data['status'] ?? '', ['draft', 'published', 'archived'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Status must be draft, published or archived'
            ]);
        }

        if (!in_array($data['level'] ?? '', ['beginner', 'intermediate', 'advanced'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Level must be beginner, intermediate or advanced'
            ]);
        }

        // Get admin id from JWT token
        $authUser = session()->get('auth_user');

        $courseData = [
            'category_id'           => $data['category_id'],
            'created_by'            => $authUser->uid ?? 1,
            'title'                 => $data['title'],
            'description'           => $data['description'] ?? null,
            'thumbnail'             => $data['thumbnail'] ?? null,
            'price'                 => $data['price'] ?? 0,
            'level'                 => $data['level'] ?? 'beginner',
            'status'                => $data['status'] ?? 'draft',
            'quiz_duration_minutes' => $data['quiz_duration_minutes'] ?? 0,
        ];

        $this->courseService->createCourse($courseData);

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Course created successfully'
        ]);
    }

    // PUT update course
    public function update($id)
    {
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Course not found'
            ]);
        }

        $data = $this->request->getJSON(true);

        $courseData = [
            'category_id'           => $data['category_id'] ?? $course['category_id'],
            'title'                 => $data['title'] ?? $course['title'],
            'description'           => $data['description'] ?? $course['description'],
            'thumbnail'             => $data['thumbnail'] ?? $course['thumbnail'],
            'price'                 => $data['price'] ?? $course['price'],
            'level'                 => $data['level'] ?? $course['level'],
            'status'                => $data['status'] ?? $course['status'],
            'quiz_duration_minutes' => $data['quiz_duration_minutes'] ?? $course['quiz_duration_minutes'],
        ];

        $this->courseService->updateCourse($id, $courseData);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Course updated successfully'
        ]);
    }

    // DELETE course
    public function delete($id)
    {
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Course not found'
            ]);
        }

        $this->courseService->deleteCourse($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Course deleted successfully'
        ]);
    }
}