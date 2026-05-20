<?php
namespace App\Services;

use App\Models\QuizAttemptModel;
use App\Models\QuizAttemptAnswerModel;
use App\Models\QuizQuestionModel;
use App\Models\QuizModel;
use Config\Database;

class QuizAttemptService
{
    protected $attemptModel;
    protected $answerModel;
    protected $questionModel;
    protected $quizModel;
    protected $db;

    public function __construct()
    {
        $this->attemptModel  = new QuizAttemptModel();
        $this->answerModel   = new QuizAttemptAnswerModel();
        $this->questionModel = new QuizQuestionModel();
        $this->quizModel     = new QuizModel();
        $this->db            = Database::connect();
    }

    public function submitQuiz($studentId, $quizId, $answers)
{
    $quiz = $this->quizModel->find($quizId);

    if (!$quiz) {
        throw new \Exception('Invalid quiz.');
    }

    /*
    | Prevent Retake After Pass
    */
    $alreadyPassed = $this->attemptModel
        ->where('quiz_id', $quizId)
        ->where('user_id', $studentId)
        ->where('passed', 1)
        ->first();

    if ($alreadyPassed) {
        throw new \Exception('You have already passed this quiz.');
    }

    /*
    | Timer Validation — skipped (quiz runs on frontend JS, no server session)
    */
    $startedAt = date('Y-m-d H:i:s');

    /*
    | Fetch Questions
    */
    $questions = $this->questionModel
        ->where('quiz_id', $quizId)
        ->findAll();

    if (empty($questions)) {
        throw new \Exception('No questions found.');
    }

    shuffle($questions);

    // ✅ Reuse attempt count
    $attemptCount = $this->attemptModel
        ->where('quiz_id', $quizId)
        ->where('user_id', $studentId)
        ->countAllResults();

    $totalScore = 0;
    $maxMarks   = 0;
    foreach ($questions as $q) {
        $maxMarks += (int) ($q['marks'] ?? 1);
    }

    $this->db->transStart();

    /*
    | Create Attempt
    */
    $attemptId = $this->attemptModel->insert([
        'quiz_id'        => $quizId,
        'user_id'        => $studentId,
        'score'          => 0,
        'passed'         => 0,
        'attempted_at'   => date('Y-m-d H:i:s'),
        'started_at'     => $startedAt,
        'total_questions'=> count($questions),
        'ip_address'     => service('request')->getIPAddress()
    ]);

    /*
    | Evaluate Answers
    */
    foreach ($questions as $q) {
        $selected = $answers[$q['id']] ?? null;

        if (!in_array($selected, ['A','B','C','D'])) {
            $selected = null;
        }

        $isCorrect = ($selected === $q['correct_option']) ? 1 : 0;

        // ✅ Default marks to 1 if not set
        $marks = $isCorrect ? ($q['marks'] ?? 1) : 0;

        $totalScore += $marks;

        $this->answerModel->insert([
            'attempt_id'     => $attemptId,
            'question_id'    => $q['id'],
            'selected_option'=> $selected,
            'is_correct'     => $isCorrect,
            'marks_awarded'  => $marks
        ]);
    }

    $passed = ($totalScore >= $quiz['passing_marks']) ? 1 : 0;

    /*
    | Finalize Attempt
    */
    $completedAt = date('Y-m-d H:i:s');
    $timeTaken = strtotime($completedAt) - strtotime($startedAt);

    $this->attemptModel->update($attemptId, [
        'score'           => $totalScore,
        'passed'          => $passed,
        'completed_at'    => $completedAt,
        'time_taken'      => $timeTaken,
        'result'          => $passed ? 'pass' : 'fail',
        'obtained_marks'  => $totalScore,
        'total_marks'     => $maxMarks,
    ]);

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        throw new \Exception('Quiz submission failed.');
    }

    /*
    | Update Best Attempt
    */
    $this->updateBestAttempt($studentId, $quizId);

    /*
    | Certificate Generation (disabled - now happens after payment, not after quiz)
    |
    | Note: Certificate is generated after successful Razorpay payment in
    | UserActivationService to ensure only paid users receive certificates.
    */

    /*
    | Return Analytics
    */
    $correctAnswers = count(array_filter($questions, function ($q) use ($answers) {
        return isset($answers[$q['id']]) &&
               $answers[$q['id']] === $q['correct_option'];
    }));

    return [
        'total_score'     => $totalScore,
        'passed'          => $passed,
        'correct_answers' => $correctAnswers,
        'wrong_answers'   => count($questions) - $correctAnswers,
        'attempt_number'  => $attemptCount + 1, // ✅ reused variable
        'time_taken'      => $timeTaken
    ];
}


    private function updateBestAttempt($studentId, $quizId)
    {
        $this->attemptModel
            ->where('quiz_id', $quizId)
            ->where('user_id', $studentId)
            ->set(['best_attempt' => 0])
            ->update();

        $best = $this->attemptModel
            ->where('quiz_id', $quizId)
            ->where('user_id', $studentId)
            ->orderBy('score', 'DESC')
            ->orderBy('time_taken', 'ASC')
            ->first();

        if ($best) {
            $this->attemptModel->update($best['id'], [
                'best_attempt' => 1
            ]);
        }
    }
}