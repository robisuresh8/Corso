<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JWTService;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwtService = new JWTService();
        $token = $jwtService->extractToken($request);

        if (!$token) {
            return service('response')
                ->setJSON(['error' => 'Token not provided'])
                ->setStatusCode(401);
        }

        $decoded = $jwtService->validateToken($token);

        if (!$decoded) {
            return service('response')
                ->setJSON(['error' => 'Invalid or expired token'])
                ->setStatusCode(401);
        }

        // Store user info in UserService for use in controllers
        \App\Libraries\UserService::setCurrentUser($decoded);
        
        // Also set on request object as fallback
        $request->user_id = $decoded['user_id'];
        $request->user_email = $decoded['email'];
        $request->user_name = $decoded['name'];

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
