<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\JWTService;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1️⃣ Check Authorization header exists
        $header = $request->getHeaderLine('Authorization');

        if (!$header) {
            return service('response')
                ->setJSON(['error' => 'Token required'])
                ->setStatusCode(401);
        }

        // 2️⃣ Extract token
        $token = str_replace('Bearer ', '', $header);

        try {
            // 3️⃣ Verify JWT token
            $jwt = new JWTService();
            $decoded = $jwt->verifyToken($token);

            // 4️⃣ Check role can access admin (super_admin, admin, or hr – hr visibility is controlled by permissions in UI)
            $role = $decoded->role ?? '';
            if (!in_array($role, ['admin', 'super_admin', 'hr'], true)) {
                return service('response')
                    ->setJSON(['error' => 'Access denied.'])
                    ->setStatusCode(403);
            }

            // 5️⃣ Store in session for use in controllers
            session()->set('auth_user', $decoded);

        } catch (\Exception $e) {
            return service('response')
                ->setJSON(['error' => 'Invalid or expired token'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed
    }
}