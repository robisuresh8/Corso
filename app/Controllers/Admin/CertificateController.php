<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CertificateService;

class CertificateController extends BaseController
{
    protected $certificateService;

    public function __construct()
    {
        $this->certificateService = new CertificateService();
    }

    // List all certificates
    public function index()
    {
        $certificates = $this->certificateService->getAllCertificates();
        return view('admin/certificate/index', ['certificates' => $certificates]);
    }

    // View certificate details
    public function view($id)
    {
        $certificate = $this->certificateService->getCertificateById($id);
        if (!$certificate) {
            return redirect()->to('/admin/certificates')->with('error', 'Certificate not found.');
        }
        return view('admin/certificate/view', ['certificate' => $certificate]);
    }

    // Revoke certificate
    public function revoke($id)
    {
        $this->certificateService->revokeCertificate($id);
        return redirect()->to('/admin/certificates')->with('message', 'Certificate revoked successfully!');
    }

    // Delete certificate
    public function delete($id)
    {
        $this->certificateService->deleteCertificate($id);
        return redirect()->to('/admin/certificates')->with('message', 'Certificate deleted successfully!');
    }
}
