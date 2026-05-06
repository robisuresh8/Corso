<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QuizQuestionModel;
use App\Models\QuizModel;

class QuestionController extends BaseController
{
    protected $questionModel;
    protected $quizModel;

    public function __construct()
    {
        $this->questionModel = new QuizQuestionModel();
        $this->quizModel     = new QuizModel();
    }

    // GET all questions of a quiz
    public function index($quizId)
    {
        $quiz = $this->quizModel->find($quizId);

        if (!$quiz) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Quiz not found'
            ]);
        }

        $questions = $this->questionModel
            ->where('quiz_id', $quizId)
            ->orderBy('position', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'quiz'      => $quiz,
                'questions' => $questions
            ]
        ]);
    }

    // GET single question for editing
    public function edit($id)
    {
        $question = $this->questionModel->find($id);

        if (!$question) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Question not found'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $question
        ]);
    }

    // POST store new question
    public function store($quizId)
    {
        $quiz = $this->quizModel->find($quizId);

        if (!$quiz) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Quiz not found'
            ]);
        }

        $data = $this->request->getJSON(true);

        // Validation
        if (empty($data['question'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Question is required'
            ]);
        }

        foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $option) {
            if (empty($data[$option])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => ucfirst(str_replace('_', ' ', $option)) . ' is required'
                ]);
            }
        }

        if (empty($data['correct_option']) || !in_array($data['correct_option'], ['A', 'B', 'C', 'D'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Correct option must be A, B, C or D'
            ]);
        }

        if (empty($data['marks']) || !is_numeric($data['marks']) || $data['marks'] <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Marks must be a number greater than 0'
            ]);
        }

        // Auto position
        $lastPosition = $this->questionModel
            ->where('quiz_id', $quizId)
            ->selectMax('position')
            ->first()['position'] ?? 0;

        $this->questionModel->insert([
            'quiz_id'        => $quizId,
            'question'       => $data['question'],
            'option_a'       => $data['option_a'],
            'option_b'       => $data['option_b'],
            'option_c'       => $data['option_c'],
            'option_d'       => $data['option_d'],
            'correct_option' => $data['correct_option'],
            'marks'          => $data['marks'],
            'position'       => $lastPosition + 1
        ]);

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => 'success',
            'message' => 'Question added successfully'
        ]);
    }

    // PUT update question
    public function update($id)
    {
        $question = $this->questionModel->find($id);

        if (!$question) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Question not found'
            ]);
        }

        $data = $this->request->getJSON(true);

        $this->questionModel->update($id, [
            'question'       => $data['question']       ?? $question['question'],
            'option_a'       => $data['option_a']       ?? $question['option_a'],
            'option_b'       => $data['option_b']       ?? $question['option_b'],
            'option_c'       => $data['option_c']       ?? $question['option_c'],
            'option_d'       => $data['option_d']       ?? $question['option_d'],
            'correct_option' => $data['correct_option'] ?? $question['correct_option'],
            'marks'          => $data['marks']          ?? $question['marks'],
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Question updated successfully'
        ]);
    }

    // DELETE question
    public function delete($id)
    {
        $question = $this->questionModel->find($id);

        if (!$question) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Question not found'
            ]);
        }

        $this->questionModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Question deleted successfully'
        ]);
    }
}