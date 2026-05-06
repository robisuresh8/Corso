<?php

namespace App\Services;

use App\Models\CourseModel;

class CourseService
{
    protected $courseModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN METHODS
    |--------------------------------------------------------------------------
    */

    // Get all courses (Admin)
    public function getAllCourses()
    {
        return $this->courseModel
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    // Create new course
    public function createCourse(array $data)
    {
        // Generate unique slug
        if (isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        return $this->courseModel->insert($data);
    }

    // Update existing course
    public function updateCourse(int $id, array $data)
    {
        if (isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
        }

        return $this->courseModel->update($id, $data);
    }

    // Soft delete course
  public function deleteCourse(int $id)
{
    return $this->courseModel->delete($id, true); // ← true = hard delete
}

    /*
    |--------------------------------------------------------------------------
    | SHARED METHODS
    |--------------------------------------------------------------------------
    */

    // Get course by ID
    public function getCourseById(int $id)
    {
        return $this->courseModel->find($id);
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT METHODS
    |--------------------------------------------------------------------------
    */

    // Get only published courses
    public function getPublishedCourses()
    {
        return $this->courseModel
            ->where('status', 'published')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    // Get courses a student is enrolled in
    public function getEnrolledCourses(int $userId)
    {
        return $this->courseModel
            ->select('courses.*')
            ->join('enrollments', 'enrollments.course_id = courses.id')
            ->where('enrollments.user_id', $userId)
            ->where('courses.status', 'published')
            ->orderBy('courses.id', 'DESC')
            ->findAll();
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPERS
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(string $title, ?int $ignoreId = null)
{
    $slug = url_title($title, '-', true);
    $originalSlug = $slug;
    $counter = 1;

    while (
        $this->courseModel
            ->where('slug', $slug)
            ->where('id !=', $ignoreId ?? 0)
            ->withDeleted() // ← check even soft deleted
            ->first()
    ) {
        $slug = $originalSlug . '-' . $counter++;
    }

    return $slug;
}
}
