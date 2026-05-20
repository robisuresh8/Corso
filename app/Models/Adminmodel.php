<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admins';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'full_name',
        'email',
        'password',
        'password_reset_required',
        'role',
        'status',
        'last_login',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        return $data;
    }

    public function getAdminPermissions(int $adminId): array
    {
        return $this->db->table('admins as a')
            ->select('p.name as permission_name, p.slug')
            ->join('user_permissions up', 'up.user_id = a.id')
            ->join('permissions p', 'p.id = up.permission_id')
            ->where('a.id', $adminId)
            ->get()
            ->getResultArray();
    }

    public function getDashboardStats(): array
    {
        $db = \Config\Database::connect();
        return [
            'total_students'  => $db->table('users')->where('role', 'student')->countAllResults(),
            'total_courses'   => $db->table('courses')->countAll(),
            'total_quizzes'   => $db->table('quizzes')->countAll(),
            'recent_attempts' => $db->table('quiz_attempts')->countAll(),
        ];
    }
}