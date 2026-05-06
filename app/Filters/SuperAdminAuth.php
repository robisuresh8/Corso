<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Libraries\JWTService;

class SuperAdminAuth implements FilterInterface
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
            if (($decoded->role ?? '') !== 'super_admin') {
                return service('response')
                    ->setJSON(['error' => 'Super admin access only'])
                    ->setStatusCode(403);
            }
            session()->set('auth_user', $decoded);
        } catch (\Exception $e) {
            return service('response')
                ->setJSON(['error' => 'Invalid or expired token'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
