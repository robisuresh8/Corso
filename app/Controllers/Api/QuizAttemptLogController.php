<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\QuizModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Logs homepage skill-check attempts into quiz_attempts so admin stats stay accurate.
 */
class QuizAttemptLogController extends BaseController
{
    /**
     * POST /api/quiz-attempts/log (JWT)
     * Body: { "course_title": "...", "score": 8, "total": 10 }
     */
    public function log(): ResponseInterface
    {
        $uid = (int) ($this->request->user_id ?? 0);
        if ($uid <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $data = $this->request->getJSON(true) ?? [];
        $title = trim((string) ($data['course_title'] ?? ''));
        $score = (int) ($data['score'] ?? 0);
        $total = (int) ($data['total'] ?? 0);
        if ($title === '' || $total < 1) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'course_title and total are required']);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('quiz_attempts')) {
            return $this->response->setJSON(['ok' => false, 'reason' => 'no_table']);
        }

        $norm = strtolower(trim($title));
        $course = $db->query(
            'SELECT id FROM courses WHERE LOWER(TRIM(title)) = ? LIMIT 1',
            [$norm]
        )->getRowArray();

        if (!$course) {
            return $this->response->setJSON(['ok' => true, 'logged' => false, 'reason' => 'course_not_found']);
        }

        $quizModel = new QuizModel();
        $quiz = $quizModel->where('course_id', (int) $course['id'])->orderBy('id', 'ASC')->first();
        if (!$quiz) {
            return $this->response->setJSON(['ok' => true, 'logged' => false, 'reason' => 'quiz_not_found']);
        }

        $passingMarks = (int) ($quiz['passing_marks'] ?? 1);
        $totalMarks = (int) ($quiz['total_marks'] ?? $total);
        if ($totalMarks < 1) {
            $totalMarks = $total;
        }
        $passed = $score >= $passingMarks ? 1 : 0;
        $now = date('Y-m-d H:i:s');
        $ip = $this->request->getIPAddress();

        $insert = [
            'quiz_id' => (int) $quiz['id'],
            'user_id' => $uid,
            'score' => $score,
            'passed' => $passed,
            'attempted_at' => $now,
            'best_attempt' => 0,
            'started_at' => $now,
            'total_questions' => $total,
            'completed_at' => $now,
            'time_taken' => 0,
            'ip_address' => $ip,
        ];
        if ($db->fieldExists('result', 'quiz_attempts')) {
            $insert['result'] = $passed ? 'pass' : 'fail';
        }
        if ($db->fieldExists('obtained_marks', 'quiz_attempts')) {
            $insert['obtained_marks'] = $score;
        }
        if ($db->fieldExists('total_marks', 'quiz_attempts')) {
            $insert['total_marks'] = $totalMarks;
        }

        try {
            $db->table('quiz_attempts')->insert($insert);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not save attempt']);
        }

        return $this->response->setJSON([
            'ok' => true,
            'logged' => true,
            'id' => (int) $db->insertID(),
        ]);
    }
}
