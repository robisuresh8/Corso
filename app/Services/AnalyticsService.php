<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class AnalyticsService
{
    protected $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /* 1️⃣ Monthly User Growth */
    public function monthlyUsers(): array
    {
        try {
            return $this->db->table('users')
                ->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(id) as total")
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /* 2️⃣ Monthly Revenue */
    public function monthlyRevenue(): array
    {
        try {
            return $this->db->table('payments')
                ->select("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
                ->whereIn('status', ['paid', 'success'])
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /* 3️⃣ Monthly Quiz Attempts */
    public function monthlyAttempts(): array
    {
        try {
            return $this->db->table('quiz_attempts')
                ->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(id) as total")
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /* 4️⃣ Monthly Pass Rate */
    public function monthlyPassRate(): array
    {
        try {
            return $this->db->table('quiz_attempts')
                ->select("DATE_FORMAT(created_at, '%Y-%m') as month")
                ->select("ROUND((SUM(CASE WHEN result = 'pass' THEN 1 ELSE 0 END) / COUNT(id)) * 100, 2) as pass_rate")
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /* 5️⃣ Top 5 Courses */
    public function topCourses(): array
    {
        try {
            return $this->db->table('enrollments e')
                ->select('c.title, COUNT(e.id) as total_enrollments')
                ->join('courses c', 'c.id = e.course_id')
                ->groupBy('c.id')
                ->orderBy('total_enrollments', 'DESC')
                ->limit(5)
                ->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /* 6️⃣ Last 7 Days Attempts */
    public function weeklyActivity(): array
    {
        try {
            return $this->db->table('quiz_attempts')
                ->select("DATE(created_at) as day, COUNT(id) as total")
                ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
                ->groupBy('day')
                ->orderBy('day', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}