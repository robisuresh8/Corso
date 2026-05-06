<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'password',
        'password_hash',
        'role',
        'status',
        'email_verified',
        'email_verified_at',
        'verification_token',
        'activation_token',
        'last_login_at',
        'reset_token',
        'reset_expires',
        'force_password_change',
        'forgot_password_expires_at',
        'temp_password_source',
    ];

    protected $useTimestamps = true;  // handles created_at and updated_at automatically
}