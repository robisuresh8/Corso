<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\QuizQuestionModel;
use App\Models\QuizModel;

class QuizQuestionsApiController extends BaseController
{
    /**
     * GET /api/quiz/{quizId}/questions
     * Public endpoint — JS se questions fetch karne ke liye
     */
    public function questions($quizId)
    {
        $quizModel     = new QuizModel();
        $questionModel = new QuizQuestionModel();

        $quiz = $quizModel->find($quizId);

        if (!$quiz) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Quiz not found']);
        }

        $questions = $questionModel
            ->where('quiz_id', $quizId)
            ->orderBy('position', 'ASC')
            ->findAll();

        $safe = array_map(function($q) {
            return [
                'id'             => $q['id'],
                'question'       => $q['question'],
                'option_a'       => $q['option_a'],
                'option_b'       => $q['option_b'],
                'option_c'       => $q['option_c'],
                'option_d'       => $q['option_d'],
                'correct_option' => $q['correct_option'], // scoring ke liye
                'marks'          => $q['marks'],
            ];
        }, $questions);

        return $this->response->setJSON([
            'quiz_id'   => (int) $quizId,
            'title'     => $quiz['title'],
            'questions' => $safe,
        ]);
    }
}