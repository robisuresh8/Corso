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
        'password', // raw password visible
        'password_hash', // encrypted password
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

    protected $useTimestamps = true;

    // automatically handle password hashing
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        // check if password exists
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {

            // store encrypted password in password_hash
            $data['data']['password_hash'] = password_hash(
                $data['data']['password'],
                PASSWORD_DEFAULT
            );
        }

        return $data;
    }
}