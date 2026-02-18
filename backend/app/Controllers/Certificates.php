<?php
namespace App\Controllers;
use App\Models\CertificateModel;
use CodeIgniter\RESTful\ResourceController;

class Certificates extends ResourceController
{
    protected $format = 'json';

    private function cors()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    }

    public function index()
    {
        $this->cors();
        // Get user_id from UserService (set by JWTAuthFilter)
        $userId = \App\Libraries\UserService::getUserId() ?? $this->request->user_id ?? null;
        
        if (!$userId) {
            return $this->failUnauthorized('Authentication required');
        }
        
        $m = new CertificateModel();
        $rows = $m->where('user_id', $userId)->orderBy('issued_at', 'DESC')->findAll(50);
        return $this->respond($rows);
    }

    public function store()
    {
        $this->cors();
        // Get user_id from UserService (set by JWTAuthFilter)
        $userId = \App\Libraries\UserService::getUserId() ?? $this->request->user_id ?? null;
        
        if (!$userId) {
            return $this->failUnauthorized('Authentication required');
        }
        
        $data = $this->request->getJSON(true);
        if (!$data || empty($data['id']) || empty($data['name']) || empty($data['course'])) {
            return $this->failValidationErrors('Invalid input');
        }
        $m = new CertificateModel();
        $m->insert([
            'id' => $data['id'],
            'user_id' => $userId,
            'name' => $data['name'],
            'course' => $data['course'],
            'score' => (int) ($data['score'] ?? 0),
            'total' => (int) ($data['total'] ?? 10),
            'issued_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->respondCreated(['ok' => true]);
    }

    public function verify()
    {
        $this->cors();
        // Verify endpoint doesn't require authentication - it's public
        $id = $this->request->getGet('id');
        if (!$id) return $this->failValidationErrors('Missing id');
        $m = new CertificateModel();
        $row = $m->find($id);
        if (!$row) return $this->failNotFound('Not found');
        return $this->respond([
            'id' => $row['id'],
            'name' => $row['name'],
            'course' => $row['course'],
            'score' => $row['score'],
            'total' => $row['total'],
            'issued_at' => $row['issued_at'],
        ]);
    }
}