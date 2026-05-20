<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitorModel extends Model
{
    protected $table            = 'visitors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'cookie_token',
        'is_registered',
        'last_active',
        'expires_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByToken(string $token): ?array
    {
        return $this->where('cookie_token', $token)->first();
    }

    public function markRegistered(string $token): void
    {
        $this->where('cookie_token', $token)
             ->set(['is_registered' => 1])
             ->update();
    }

    public function touchActive(string $token): void
    {
        $this->where('cookie_token', $token)
             ->set(['last_active' => date('Y-m-d H:i:s')])
             ->update();
    }
}