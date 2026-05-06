<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JWTService;

class JWTAuth implements FilterInterface
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

            // Store user data in session
            session()->set('auth_user', $decoded);

            $uid = is_object($decoded) ? (int) ($decoded->uid ?? 0) : 0;
            if ($uid > 0) {
                \App\Libraries\UserService::setCurrentUser([
                    'user_id' => $uid,
                    'uid'       => $uid,
                    'role'      => is_object($decoded) ? (string) ($decoded->role ?? 'student') : 'student',
                ]);
            }
            $request->user_id = $uid;

        } catch (\Exception $e) {
            return service('response')
                ->setJSON(['error' => 'Invalid token'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed here
    }
}
