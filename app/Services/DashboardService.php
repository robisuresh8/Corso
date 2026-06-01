<?php

namespace App\Services;

class DashboardService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getKPIData(): array
    {
        return [
            'total_users'       => $this->getTotalUsers(),
            'active_users'      => $this->getActiveUsers(),
            'inactive_users'    => $this->getInactiveUsers(),
            'total_students'    => $this->getUsersByRole('student'),
            'total_courses'     => $this->getTotal('courses'),
            'total_quizzes'     => $this->getTotal('quizzes'),
            'total_attempts'    => $this->getTotal('quiz_attempts'),
            'pass_rate'         => $this->getPassRate(),
            'average_score'     => $this->getAverageScore(),
            'revenue'           => $this->getRevenue(),
        ];
    }

    // ✅ Generic table count
    private function getTotal(string $table): int
    {
        try {
            return $this->db->table($table)->countAll();
        } catch (\Exception $e) {
            return 0;
        }
    }

    // ✅ Total users
    private function getTotalUsers(): int
    {
        return $this->db->table('users')->countAll();
    }

    // ✅ Fixed: status is enum string not 0/1
    private function getActiveUsers(): int
    {
        return $this->db->table('users')
            ->where('status', 'active')
            ->countAllResults();
    }

    // ✅ Fixed: status is enum string not 0/1
    // visitors table (pre-registered, not yet paid) bhi count karo
    private function getInactiveUsers(): int
    {
        $usersInactive = $this->db->table('users')
            ->where('status', 'inactive')
            ->countAllResults();

        $visitorsCount = 0;
        try {
            $visitorsCount = $this->db->table('visitors')
                ->where('is_registered', 0)
                ->countAllResults();
        } catch (\Exception $e) {}

        return $usersInactive + $visitorsCount;
    }

    // ✅ Get users by role
    private function getUsersByRole(string $role): int
    {
        return $this->db->table('users')
            ->where('role', $role)
            ->countAllResults();
    }

    // ✅ Pass rate with zero division protection
    private function getPassRate(): float
    {
        try {
            $total = $this->db->table('quiz_attempts')->countAll();

            if ($total === 0) return 0.0;

            $passed = $this->db->table('quiz_attempts')
                ->where('result', 'pass')
                ->countAllResults();

            return round(($passed / $total) * 100, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // ✅ Average score with zero division protection
    private function getAverageScore(): float
    {
        try {
            $result = $this->db->query("
                SELECT 
                    SUM(obtained_marks) as total_obtained,
                    SUM(total_marks) as total_marks
                FROM quiz_attempts
            ")->getRow();

            if (!$result || !$result->total_marks || $result->total_marks == 0) {
                return 0.0;
            }

            return round(($result->total_obtained / $result->total_marks) * 100, 2);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // ✅ Revenue with null protection
    private function getRevenue(): float
    {
        try {
            $result = $this->db->table('payments')
                ->selectSum('amount')
                ->whereIn('status', ['paid', 'success'])
                ->get()
                ->getRow();

            return (float)($result->amount ?? 0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}