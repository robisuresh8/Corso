<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\QuizService;
use App\Services\QuizAttemptService;
use App\Services\CourseService;

class QuizController extends BaseController
{
    protected $quizService;
    protected $attemptService;
    protected $courseService;

    public function __construct()
    {
        $this->quizService    = new QuizService();
        $this->attemptService = new QuizAttemptService();
        $this->courseService  = new CourseService();
    }

    /**
     * Show quiz with questions fetched from DB
     * URL: GET /student/quiz/{quizId}
     */
    public function index($quizId)
    {
        // JWT se studentId lo (JWTAuth filter sets request->user_id)
        $studentId = $this->request->user_id ?? null;
        if (!$studentId) {
            $decoded = session()->get('auth_user');
            $studentId = $decoded ? (int)(is_object($decoded) ? ($decoded->uid ?? 0) : ($decoded['uid'] ?? 0)) : null;
        }

        // DB se quiz + questions fetch karo
        $quiz = $this->quizService->getQuizWithQuestionsByQuizId((int) $quizId);

        if (!$quiz) {
            return redirect()->back()
                ->with('error', 'Quiz not found.');
        }

        // Check: student us course mein enrolled hai ya nahi
        $enrolledCourses   = $this->courseService->getEnrolledCourses($studentId);
        $enrolledCourseIds = array_column($enrolledCourses, 'id');

        if (!in_array($quiz['course_id'], $enrolledCourseIds)) {
            return redirect()->to('/student/courses')
                ->with('error', 'You are not enrolled in this course.');
        }

        return view('student/quiz/index', [
            'quiz'      => $quiz,
            'questions' => $quiz['questions'],
        ]);
    }

    /**
     * Submit quiz answers
     * URL: POST /student/quiz/{quizId}/submit
     */
    public function submit($quizId)
    {
        // JWT se studentId lo (JWTAuth filter sets request->user_id)
        $studentId = $this->request->user_id ?? null;
        if (!$studentId) {
            $decoded = session()->get('auth_user');
            $studentId = $decoded ? (int)(is_object($decoded) ? ($decoded->uid ?? 0) : ($decoded['uid'] ?? 0)) : null;
        }
        $answers   = $this->request->getPost('answers');

        if (empty($answers)) {
            return redirect()->back()
                ->with('error', 'Please answer the questions.');
        }

        $result = $this->attemptService->submitQuiz(
            $studentId,
            (int) $quizId,
            $answers
        );

        if ($result['passed']) {
            return redirect()->to('/student/courses')
                ->with('message', 'Quiz passed! Score: ' . $result['total_score']);
        }

        return redirect()->to('/student/courses')
            ->with('error', 'Quiz failed. Score: ' . $result['total_score']);
    }
}