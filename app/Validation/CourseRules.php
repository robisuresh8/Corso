<?php
namespace App\Validation;

class CourseRules
{
    public $createCourse = [
        'title' => 'required|min_length[3]|max_length[200]',
        'category_id' => 'required|numeric',
        'status' => 'required|in_list[draft,published,archived]',
        'level' => 'required|in_list[beginner,intermediate,advanced]',
        'price' => 'permit_empty|decimal',
        'quiz_duration_minutes' => 'permit_empty|integer'
    ];

    public $updateCourse = [
        'title' => 'required|min_length[3]|max_length[200]',
        'category_id' => 'required|numeric',
        'status' => 'required|in_list[draft,published,archived]',
        'level' => 'required|in_list[beginner,intermediate,advanced]',
        'price' => 'permit_empty|decimal',
        'quiz_duration_minutes' => 'permit_empty|integer'
    ];
}
