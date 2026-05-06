<?php
namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Services\CertificateService;

class CertificateController extends BaseController
{
    protected $certificateService;

    public function __construct()
    {
        $this->certificateService = new CertificateService();
    }

    public function index()
    {
        $userId = session()->get('user_id');

        $certificates = $this->certificateService
            ->getCertificatesByUser($userId);

        return view('student/certificates/index', [
            'certificates' => $certificates
        ]);
    }

    public function download($id)
    {
        try {
            // Support both session (web) and JWT (API/fetch)
            $userId = session()->get('user_id')
                ?? \App\Libraries\UserService::getUserId()
                ?? $this->request->user_id
                ?? null;

            log_message('debug', 'Certificate download: id=' . $id . ', userId=' . ($userId ?? 'null'));

            if (!$userId) {
                return $this->response->setStatusCode(401)->setJSON(['error' => 'Authentication required']);
            }

            $certificate = $this->certificateService
                ->getUserCertificate($id, $userId);

            log_message('debug', 'Certificate lookup: ' . ($certificate ? 'found' : 'not found'));

            if (!$certificate) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Certificate not found for user']);
            }

            if (empty($certificate['certificate_path'])) {
                log_message('debug', 'Certificate has no path, regenerating...');
                $certificate = $this->certificateService->regenerateCertificateFile((int) $id);
            }

            if (!$certificate || empty($certificate['certificate_path'])) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Certificate file could not be generated']);
            }

            $absolutePath = WRITEPATH . $certificate['certificate_path'];
            log_message('debug', 'Certificate path: ' . $absolutePath);

            if (!is_file($absolutePath)) {
                log_message('debug', 'File not found, regenerating...');
                $certificate = $this->certificateService->regenerateCertificateFile((int) $id);
                $absolutePath = WRITEPATH . ($certificate['certificate_path'] ?? '');
            }

            if (!is_file($absolutePath)) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Certificate file is missing at: ' . $absolutePath]);
            }

            $certModel = new \App\Models\CertificateModel();
            if (array_key_exists('download_count', $certificate)) {
                $certModel->set('download_count', 'download_count + 1', false)
                    ->where('id', (int) $id)
                    ->update();
            }

            // Return file with proper headers for fetch API
            $filename = 'corso-certificate-' . ($certificate['certificate_number'] ?? $id) . '.pdf';
            $content = file_get_contents($absolutePath);
            if ($content === false) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Could not read certificate file']);
            }

            return $this->response
                ->setContentType('application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($content);
        } catch (\Throwable $e) {
            log_message('error', 'Certificate download error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
