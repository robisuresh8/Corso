<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private $secret;
    private $expires;

    public function __construct()
    {
        $this->secret = getenv('jwt.secret') ?: getenv('JWT_SECRET') ?: 'corso-jwt-secret-key-change-in-production';
        $this->expires = (int) (getenv('jwt.expires') ?: getenv('JWT_EXPIRES') ?: 86400);
    }

    public function generateToken($user)
    {
        $payload = [
            'iss' => base_url(),
            'iat' => time(),
            'exp' => time() + $this->expires,
            'uid' => $user['id'] ?? 0,
            'email' => $user['email'] ?? '',
            'name' => $user['name'] ?? '',
            'role' => $user['role'] ?? 'student',
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function verifyToken($token)
    {
        return JWT::decode($token, new Key($this->secret, 'HS256'));
    }
}
