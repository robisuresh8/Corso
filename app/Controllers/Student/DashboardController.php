<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * GET /api/student/dashboard
     */
    public function index()
    {
        $userId = (int) $this->getJwtUserId();
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();

        // 1. Certificates earned
        $certCount = 0;
        try {
            $certCount = $db->table('certificates')
                ->where('user_id', $userId)
                ->countAllResults();
        } catch (\Throwable $e) {}

        // 2. Average score (%)
        $avgScore = 0;
        try {
            $attempts = $db->table('quiz_attempts')
                ->where('user_id', $userId)
                ->get()->getResultArray();
            if (count($attempts) > 0) {
                $totalObtained = array_sum(array_column($attempts, 'obtained_marks'));
                $totalMarks    = array_sum(array_column($attempts, 'total_marks'));
                $avgScore = $totalMarks > 0 ? round(($totalObtained / $totalMarks) * 100) : 0;
            }
        } catch (\Throwable $e) {}

        // 3. Skill checks passed
        $skillsPassed = 0;
        try {
            $skillsPassed = $db->table('quiz_attempts')
                ->where('user_id', $userId)
                ->where('result', 'pass')
                ->countAllResults();
        } catch (\Throwable $e) {}

        // 4. Lessons this week — enrolled courses jo is week mein hain
        $lessonsThisWeek = 0;
        try {
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            // enrollments table mein updated_at nahi hai, enrolled_at use karo
            $lessonsThisWeek = $db->table('enrollments')
                ->where('user_id', $userId)
                ->where('enrolled_at >=', $weekStart)
                ->countAllResults();
        } catch (\Throwable $e) {}

        // 5. My courses with progress (progress_percent column)
        $courses = [];
        try {
            $enrollments = $db->table('enrollments')
                ->select('enrollments.progress_percent, courses.title, courses.id as course_id')
                ->join('courses', 'courses.id = enrollments.course_id')
                ->where('enrollments.user_id', $userId)
                ->orderBy('enrollments.enrolled_at', 'DESC')
                ->get()->getResultArray();

            foreach ($enrollments as $e) {
                $courses[] = [
                    'id'       => (int) $e['course_id'],
                    'title'    => $e['title'],
                    'progress' => (int) round($e['progress_percent']),
                ];
            }
        } catch (\Throwable $e) {}

        // 6. Recent certificates
        $recentCerts = [];
        try {
            $certs = $db->table('certificates')
                ->select('certificates.id, certificates.certificate_number, certificates.issued_at, certificates.created_at, courses.title as course_title')
                ->join('courses', 'courses.id = certificates.course_id', 'left')
                ->where('certificates.user_id', $userId)
                ->orderBy('certificates.issued_at', 'DESC')
                ->limit(3)
                ->get()->getResultArray();

            foreach ($certs as $c) {
                $recentCerts[] = [
                    'id'                 => (int) $c['id'],
                    'certificate_number' => $c['certificate_number'],
                    'course_title'       => $c['course_title'] ?? '',
                    'issued_at'          => $c['issued_at'] ?? $c['created_at'],
                ];
            }
        } catch (\Throwable $e) {}

        return $this->response->setJSON([
            'certificates_earned' => $certCount,
            'avg_score'           => $avgScore,
            'skills_passed'       => $skillsPassed,
            'lessons_this_week'   => $lessonsThisWeek,
            'courses'             => $courses,
            'recent_certificates' => $recentCerts,
        ]);
    }

    private function getJwtUserId(): ?int
    {
        try {
            $header = $this->request->getHeaderLine('Authorization');
            $token  = str_replace('Bearer ', '', $header);
            if (!$token) return null;
            $parts   = explode('.', $token);
            if (count($parts) !== 3) return null;
            $payload = json_decode(base64_decode(
                str_replace(['-', '_'], ['+', '/'], $parts[1])
            ), true);
            return isset($payload['uid']) ? (int) $payload['uid'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}