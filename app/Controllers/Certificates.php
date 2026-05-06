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
        
        log_message('debug', 'Certificates::index - userId=' . ($userId ?? 'null') . ', Authorization header=' . substr($this->request->getHeaderLine('Authorization'), 0, 20));
        
        if (!$userId) {
            return $this->failUnauthorized('Authentication required');
        }
        
        $m = new CertificateModel();
        $rows = $m->where('user_id', $userId)->orderBy('issued_at', 'DESC')->findAll(50);
        log_message('debug', 'Certificates::index - Found ' . count($rows) . ' certificates for user ' . $userId);
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
        if (!$data || empty($data['name'])) {
            return $this->failValidationErrors('Invalid input');
        }

        $course = trim((string) ($data['course'] ?? $data['course_name'] ?? 'General'));
        if ($course === '') {
            $course = 'General';
        }

        $m = new CertificateModel();
        $insert = [
            'user_id' => $userId,
            'name' => $data['name'],
            'course' => $course,
            'score' => (int) ($data['score'] ?? 0),
            'total' => (int) ($data['total'] ?? 10),
            'issued_at' => date('Y-m-d H:i:s'),
        ];

        // Support modern schema if these columns exist.
        $db = \Config\Database::connect();
        if ($db->fieldExists('certificate_number', 'certificates')) {
            $insert['certificate_number'] = 'CERT-' . strtoupper(bin2hex(random_bytes(4)));
        }
        if ($db->fieldExists('course_name', 'certificates')) {
            $insert['course_name'] = $course;
        }
        if ($db->fieldExists('user_name', 'certificates')) {
            $insert['user_name'] = (string) $data['name'];
        }
        if ($db->fieldExists('status', 'certificates')) {
            $insert['status'] = 'active';
        }

        $id = $m->insert($insert);
        if (!$id) {
            return $this->respond(['error' => 'Certificate could not be saved'], 500);
        }

        return $this->respondCreated([
            'ok' => true,
            'id' => $id,
        ]);
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
        if (($row['status'] ?? 'active') !== 'active') {
            return $this->respond([
                'id' => $row['id'],
                'status' => $row['status'] ?? 'unknown',
                'valid' => false,
                'message' => 'Certificate is not active',
            ], 410);
        }

        $name = $row['name'] ?? ($row['user_name'] ?? '');
        $course = $row['course'] ?? ($row['course_name'] ?? '');

        return $this->respond([
            'id' => $row['id'],
            'certificate_number' => $row['certificate_number'] ?? null,
            'name' => $name,
            'course' => $course,
            'score' => $row['score'],
            'total' => $row['total'],
            'issued_at' => $row['issued_at'],
            'valid' => true,
        ]);
    }

    /**
     * Download certificate PDF by certificate number (public access for QR verification)
     */
    public function downloadByNumber($certificateNumber = null)
    {
        $this->cors();
        
        if (!$certificateNumber) {
            return $this->failValidationErrors('Certificate number is required');
        }

        $m = new CertificateModel();
        $row = $m->where('certificate_number', $certificateNumber)
                 ->where('status', 'active')
                 ->first();

        if (!$row) {
            return $this->failNotFound('Certificate not found or inactive');
        }

        // Generate/regenerate PDF if needed
        $certService = new \App\Services\CertificateService();
        if (empty($row['certificate_path'])) {
            $row = $certService->regenerateCertificateFile((int) $row['id']);
        }

        if (!$row || empty($row['certificate_path'])) {
            return $this->respond(['error' => 'Certificate file could not be generated'], 500);
        }

        $absolutePath = WRITEPATH . $row['certificate_path'];
        if (!is_file($absolutePath)) {
            $row = $certService->regenerateCertificateFile((int) $row['id']);
            $absolutePath = WRITEPATH . ($row['certificate_path'] ?? '');
        }

        if (!is_file($absolutePath)) {
            return $this->respond(['error' => 'Certificate file is missing'], 404);
        }

        // Update download count
        $m->set('download_count', 'download_count + 1', false)
          ->where('id', (int) $row['id'])
          ->update();

        return $this->response->download($absolutePath, null);
    }

    /**
     * Debug endpoint to check certificate records for current user
     */
    public function debug()
    {
        $this->cors();
        $userId = \App\Libraries\UserService::getUserId() ?? $this->request->user_id ?? null;
        
        if (!$userId) {
            return $this->respond(['error' => 'Not authenticated', 'user_id' => null]);
        }

        $m = new CertificateModel();
        $allCerts = $m->where('user_id', $userId)->findAll();
        
        return $this->respond([
            'user_id' => $userId,
            'certificate_count' => count($allCerts),
            'certificates' => $allCerts
        ]);
    }
}