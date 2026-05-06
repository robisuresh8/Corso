<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JWTService;

class StudentAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');

        if (!$header) {
            return service('response')
                ->setJSON(['error' => 'Token required'])
                ->setStatusCode(401);
        }

        $token = str_replace('Bearer ', '', $header);

        try {
            $jwt = new JWTService();
            $decoded = $jwt->verifyToken($token);

            // Check if user role is 'student'
            if (!isset($decoded->role) || $decoded->role !== 'student') {
                return service('response')
                    ->setJSON(['error' => 'Access denied: student only'])
                    ->setStatusCode(403);
            }

            // Attach user data to request
            $request->auth_user = $decoded;

        } catch (\Exception $e) {
            return service('response')
                ->setJSON(['error' => 'Invalid token'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after request
    }
}
