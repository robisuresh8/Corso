<?php
namespace App\Controllers;
use App\Models\UserModel;
use App\Libraries\JWTService;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected $format = 'json';

    private function cors()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    }

    public function register()
    {
        $this->cors();
        $data = $this->request->getJSON(true);
        if (!$data || empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            return $this->failValidationErrors('Invalid input');
        }
        $users = new UserModel();
        if ($users->where('email', $data['email'])->first()) {
            return $this->failResourceExists('Email already registered');
        }
        $users->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        return $this->respondCreated(['ok' => true]);
    }

    public function login()
    {
        $this->cors();
        $data = $this->request->getJSON(true);
        if (!$data || empty($data['email']) || empty($data['password'])) {
            return $this->failValidationErrors('Invalid input');
        }
        $users = new UserModel();
        $u = $users->where('email', $data['email'])->first();
        if (!$u || !password_verify($data['password'], $u['password_hash'])) {
            return $this->failUnauthorized('Invalid credentials');
        }
        
        // Generate JWT token
        $jwtService = new JWTService();
        $token = $jwtService->generateToken($u['id'], $u['email'], $u['name']);
        
        return $this->respond([
            'token' => $token,
            'user' => [
                'id' => $u['id'],
                'name' => $u['name'],
                'email' => $u['email']
            ]
        ]);
    }
}
