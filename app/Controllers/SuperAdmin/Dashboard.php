<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PaymentModel;
use App\Models\QuizAttemptModel;

class Dashboard extends BaseController
{
    /** GET /api/super-admin/stats */
    public function stats()
    {
        $userModel = new UserModel();
        $total = (int) $userModel->countAll();
        $byRole = [];
        if ($userModel->db->fieldExists('role', 'users')) {
            $builder = $userModel->db->table('users')->select('role')->selectCount('id', 'cnt')->groupBy('role');
            foreach ($builder->get()->getResultArray() as $row) {
                $byRole[$row['role']] = (int) $row['cnt'];
            }
        }
        $recentSignups = [];
        $orderBy = $userModel->db->fieldExists('created_at', 'users') ? 'created_at' : 'id';
        $rows = $userModel->select('id, name, email, role, created_at')->orderBy($orderBy, 'DESC')->findAll(10);
        foreach ($rows as $r) {
            $recentSignups[] = [
                'id' => $r['id'],
                'name' => $r['name'] ?? '',
                'email' => $r['email'] ?? '',
                'role' => $r['role'] ?? '',
                'created_at' => $r['created_at'] ?? '',
            ];
        }
        $db = $userModel->db;

        $revenueByMonth = [];
        if ($db->tableExists('payments') && $db->fieldExists('amount', 'payments')) {
            $paidAt = $db->fieldExists('paid_at', 'payments') ? 'paid_at' : ($db->fieldExists('created_at', 'payments') ? 'created_at' : null);
            if ($paidAt) {
                $rows = $db->table('payments')
                    ->select("DATE_FORMAT({$paidAt}, '%Y-%m') as month")
                    ->selectSum('amount', 'total')
                    ->groupBy('month')
                    ->orderBy('month', 'DESC')
                    ->get(12)
                    ->getResultArray();
                foreach ($rows as $row) {
                    if (!empty($row['month'])) $revenueByMonth[$row['month']] = (float) ($row['total'] ?? 0);
                }
            }
        }

        $attemptsByMonth = [];
        if ($db->tableExists('quiz_attempts')) {
            $dateCol = $db->fieldExists('attempted_at', 'quiz_attempts') ? 'attempted_at' : null;
            if ($dateCol) {
                $rows = $db->table('quiz_attempts')
                    ->select("DATE_FORMAT({$dateCol}, '%Y-%m') as month")
                    ->selectCount('id', 'cnt')
                    ->groupBy('month')
                    ->orderBy('month', 'DESC')
                    ->get(12)
                    ->getResultArray();
                foreach ($rows as $row) {
                    if (!empty($row['month'])) $attemptsByMonth[$row['month']] = (int) ($row['cnt'] ?? 0);
                }
            }
        }

        $revenueTotal = 0;
        if ($db->tableExists('payments') && $db->fieldExists('amount', 'payments')) {
            $row = (new PaymentModel())->selectSum('amount')->first();
            $revenueTotal = $row ? (float) ($row['amount'] ?? 0) : 0;
        }
        $attemptsTotal = $db->tableExists('quiz_attempts') ? (int) (new QuizAttemptModel())->countAll() : 0;

        return $this->response->setJSON([
            'total_users'      => $total,
            'by_role'          => $byRole,
            'recent_signups'   => $recentSignups,
            'revenue_total'    => $revenueTotal,
            'attempts_total'   => $attemptsTotal,
            'revenue_by_month' => $revenueByMonth,
            'attempts_by_month'=> $attemptsByMonth,
        ]);
    }
}
