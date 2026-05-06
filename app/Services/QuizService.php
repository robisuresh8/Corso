<?php

namespace App\Services;

use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use App\Models\CourseModel;

class QuizService
{
    protected $quizModel;
    protected $questionModel;
    protected $courseModel;

    public function __construct()
    {
        $this->quizModel = new QuizModel();
        $this->questionModel = new QuizQuestionModel();
        $this->courseModel = new CourseModel();
    }

    /*
    |--------------------------------------------------------------------------
    | QUIZ CRUD
    |--------------------------------------------------------------------------
    */

    public function getAllQuizzes()
    {
        return $this->quizModel
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function getQuizById(int $id)
    {
        return $this->quizModel->find($id);
    }

    public function getQuizzesByCourse(int $courseId)
    {
        return $this->quizModel
            ->where('course_id', $courseId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function createQuiz(array $data)
    {
        $this->validateQuizData($data);

        $data['slug'] = $this->generateUniqueSlug($data['title']);

        return $this->quizModel->insert($data);
    }

    public function updateQuiz(int $id, array $data)
    {
        $this->validateQuizData($data);

        if (isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
        }

        return $this->quizModel->update($id, $data);
    }

    public function deleteQuiz(int $id)
    {
        return $this->quizModel->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | QUESTIONS
    |--------------------------------------------------------------------------
    */

    public function getQuestions(int $quizId)
    {
        return $this->questionModel
            ->where('quiz_id', $quizId)
            ->orderBy('position', 'ASC')
            ->findAll();
    }

    public function addQuestion(array $data)
    {
        return $this->questionModel->insert($data);
    }

    public function updateQuestion(int $id, array $data)
    {
        return $this->questionModel->update($id, $data);
    }

    public function deleteQuestion(int $id)
    {
        return $this->questionModel->delete($id);
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT SIDE
    |--------------------------------------------------------------------------
    */

    public function getQuizWithQuestionsByQuizId(int $quizId)
    {
        $quiz = $this->quizModel->find($quizId);

        if (!$quiz) {
            return null;
        }

        $quiz['questions'] = $this->getQuestions($quizId);

        return $quiz;
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPERS
    |--------------------------------------------------------------------------
    */

    private function validateQuizData(array $data)
    {
        // Check course exists
        if (!isset($data['course_id']) ||
            !$this->courseModel->find($data['course_id'])) {
            throw new \Exception('Invalid course selected.');
        }

        // Check passing marks logic
        if ($data['passing_marks'] > $data['total_marks']) {
            throw new \Exception('Passing marks cannot exceed total marks.');
        }
    }

    private function generateUniqueSlug(string $title, $ignoreId = null)
    {
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $counter = 1;

        while (
            $this->quizModel
                ->where('slug', $slug)
                ->where('id !=', $ignoreId ?? 0)
                ->first()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
