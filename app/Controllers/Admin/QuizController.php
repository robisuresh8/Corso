<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\QuizService;

class QuizController extends BaseController
{
    protected QuizService $quizService;

    public function __construct()
    {
        $this->quizService = new QuizService();
    }

    // GET all quizzes
    public function index()
    {
        $quizzes = $this->quizService->getAllQuizzes();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $quizzes
        ]);
    }

    // GET single quiz for editing
    public function edit($id)
    {
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Quiz not found'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $quiz
        ]);
    }

    // POST create quiz
    public function store()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['course_id'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Course is required'
            ]);
        }

        if (empty($data['title'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Title is required'
            ]);
        }

        if (empty($data['total_marks']) || $data['total_marks'] <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Total marks must be greater than 0'
            ]);
        }

        if (!isset($data['passing_marks']) || $data['passing_marks'] < 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Passing marks is required'
            ]);
        }

        if ($data['passing_marks'] > $data['total_marks']) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Passing marks cannot exceed total marks'
            ]);
        }

        $quizData = [
            'course_id'     => (int) $data['course_id'],
            'title'         => trim($data['title']),
            'total_marks'   => (int) $data['total_marks'],
            'passing_marks' => (int) $data['passing_marks'],
            'duration'      => $data['duration'] ?? null,
        ];

        $this->quizService->createQuiz($quizData);

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Quiz created successfully'
        ]);
    }

    // PUT update quiz
    public function update($id)
    {
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Quiz not found'
            ]);
        }

        $data = $this->request->getJSON(true);

        if (isset($data['passing_marks'], $data['total_marks'])) {
            if ($data['passing_marks'] > $data['total_marks']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Passing marks cannot exceed total marks'
                ]);
            }
        }

        $quizData = [
            'course_id'     => $data['course_id']     ?? $quiz['course_id'],
            'title'         => $data['title']         ?? $quiz['title'],
            'total_marks'   => $data['total_marks']   ?? $quiz['total_marks'],
            'passing_marks' => $data['passing_marks'] ?? $quiz['passing_marks'],
            'duration'      => $data['duration']      ?? $quiz['duration'],
        ];

        $this->quizService->updateQuiz($id, $quizData);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Quiz updated successfully'
        ]);
    }

    // DELETE quiz
    public function delete($id)
    {
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Quiz not found'
            ]);
        }

        $this->quizService->deleteQuiz($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Quiz deleted successfully'
        ]);
    }
}