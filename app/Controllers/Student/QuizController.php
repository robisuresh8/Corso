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
        $this->quizService   = new QuizService();
        $this->attemptService = new QuizAttemptService();
        $this->courseService  = new CourseService();
    }

    /**
     * Show quiz with questions
     */
    public function index($quizId)
{
    $studentId = session()->get('user_id');

    $quiz = $this->quizService->getQuizWithQuestionsByQuizId($quizId);

    if (!$quiz) {
        return redirect()->back()
            ->with('error', 'Quiz not found.');
    }

    // Check if student is enrolled in the course
    $enrolledCourses = $this->courseService
        ->getEnrolledCourses($studentId);

    $enrolledCourseIds = array_column($enrolledCourses, 'id');

    if (!in_array($quiz['course_id'], $enrolledCourseIds)) {
        return redirect()->to('/student/courses')
            ->with('error', 'You are not enrolled in this course.');
    }

    return view('student/quiz/index', [
        'quiz' => $quiz,
        'questions' => $quiz['questions']
    ]);
}


    /**
     * Submit quiz
     */
    public function submit($quizId)
    {
        $studentId = session()->get('user_id');

        $answers = $this->request->getPost('answers');

        if (empty($answers)) {
            return redirect()->back()
                ->with('error', 'Please answer the questions.');
        }

        $result = $this->attemptService->submitQuiz(
            $studentId,
            $quizId,
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
