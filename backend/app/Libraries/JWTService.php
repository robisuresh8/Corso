<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\Config\Services;

class JWTService
{
    private $secret;
    private $algorithm = 'HS256';
    private $expiration = 86400; // 24 hours

    public function __construct()
    {
        $this->secret = env('jwt.secret', 'any-long-random-string-for-later-change-in-production');
    }

    /**
     * Generate JWT token for user
     */
    public function generateToken($userId, $email, $name)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expiration;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId,
            'email' => $email,
            'name' => $name,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Validate and decode JWT token
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract token from Authorization header
     */
    public function extractToken($request)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return null;
        }

        // Check if header starts with "Bearer "
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
