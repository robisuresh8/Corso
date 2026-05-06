<?php
namespace App\Services;

use App\Models\CertificateModel;
use App\Models\CourseModel;
use App\Models\UserModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;

class CertificateService
{
    protected $certificateModel;
    protected $userModel;
    protected $courseModel;

    public function __construct()
    {
        $this->certificateModel = new CertificateModel();
        $this->userModel = new UserModel();
        $this->courseModel = new CourseModel();
    }

    // ================= ADMIN SIDE =================

    public function getAllCertificates()
    {
        return $this->certificateModel
            ->orderBy('issued_at','DESC')
            ->findAll();
    }

    public function getCertificateById($id)
    {
        return $this->certificateModel->find($id);
    }

    public function issueCertificate(array $data)
    {
        $data['issued_at'] = $data['issued_at'] ?? date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'active';

        return $this->certificateModel->insert($data);
    }

    public function revokeCertificate($id)
    {
        return $this->certificateModel->update($id, [
            'status' => 'revoked',
            'revoked_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function deleteCertificate($id)
    {
        return $this->certificateModel->delete($id);
    }

    // ================= STUDENT SIDE =================

    /**
     * Auto generate certificate after payment
     */
    public function generateIfNotExists($userId, $courseId, $score = 0, $total = 10)
    {
        log_message('info', 'generateIfNotExists called: user_id=' . $userId . ', course_id=' . $courseId . ', score=' . $score);
        
        $existing = $this->certificateModel
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->first();

        // Fetch user and course data for populating certificate fields
        $user = $this->userModel->find((int) $userId);
        $course = $this->courseModel->find((int) $courseId);

        if ($existing) {
            log_message('info', 'Existing certificate found: id=' . $existing['id']);
            // Update score if provided and different
            if ($score > 0 && ((int)($existing['score'] ?? 0) !== $score || (int)($existing['total'] ?? 0) !== $total)) {
                $updateData = [
                    'score' => $score,
                    'total' => $total
                ];
                // Also update name fields if they're empty
                if (empty($existing['user_name']) && !empty($user)) {
                    $updateData['user_name'] = $user['name'] ?? '';
                    $updateData['name'] = $user['name'] ?? '';
                }
                if (empty($existing['course_name']) && !empty($course)) {
                    $updateData['course_name'] = $course['title'] ?? '';
                    $updateData['course'] = $course['title'] ?? '';
                }
                $this->certificateModel->update($existing['id'], $updateData);
                $existing['score'] = $score;
                $existing['total'] = $total;
            }
            if (empty($existing['certificate_path'])) {
                $this->generatePdfForCertificate((int) $existing['id']);
            }
            return $existing;
        }

        $certificateNumber = 'CORSO-' . strtoupper(uniqid());

        $insertData = [
            'certificate_number' => $certificateNumber,
            'user_id' => $userId,
            'course_id' => $courseId,
            'user_name' => $user['name'] ?? '',
            'course_name' => $course['title'] ?? '',
            'name' => $user['name'] ?? '',
            'course' => $course['title'] ?? '',
            'issued_at' => date('Y-m-d H:i:s'),
            'certificate_path' => null,
            'status' => 'active',
            'revoked_at' => null,
            'score' => $score,
            'total' => $total,
        ];

        log_message('info', 'Inserting new certificate: ' . json_encode($insertData));
        
        $id = $this->certificateModel->insert($insertData);

        if (!$id) {
            log_message('error', 'Certificate insert failed for user=' . $userId . ', course=' . $courseId);
            return false;
        }

        log_message('info', 'New certificate inserted with id=' . $id);
        
        $this->generatePdfForCertificate((int) $id);

        return $this->certificateModel->find($id);
    }

    /**
     * Get active certificates for user
     */
    public function getCertificatesByUser($userId)
    {
        return $this->certificateModel
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('issued_at','DESC')
            ->findAll();
    }

    /**
     * Get certificate for specific user (security check)
     */
    public function getUserCertificate($certificateId, $userId)
    {
        return $this->certificateModel
            ->where('id', $certificateId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    public function regenerateCertificateFile(int $certificateId): ?array
    {
        $certificate = $this->certificateModel->find($certificateId);
        if (!$certificate) {
            return null;
        }
        if (($certificate['status'] ?? '') !== 'active') {
            return $certificate;
        }

        $path = $this->generatePdfForCertificate($certificateId);
        if ($path !== null) {
            $certificate['certificate_path'] = $path;
        }

        return $certificate;
    }

    private function generatePdfForCertificate(int $certificateId): ?string
    {
        try {
            $certificate = $this->certificateModel->find($certificateId);
            if (!$certificate || ($certificate['status'] ?? '') !== 'active') {
                return null;
            }

            $user = $this->userModel->find((int) ($certificate['user_id'] ?? 0));
            $course = $this->courseModel->find((int) ($certificate['course_id'] ?? 0));
            if (!$user || !$course) {
                return null;
            }

            $verificationUrl = base_url('api/certificates/download/' . urlencode((string) ($certificate['certificate_number'] ?? $certificate['id'])));

            // Generate QR code — try PNG first (needs GD), fall back to SVG (no GD needed)
            $qrDataUri = null;
            try {
                $builder = new \Endroid\QrCode\Builder\Builder(
                    data: $verificationUrl,
                    size: 220,
                    margin: 6
                );
                $qrResult = $builder->build();
                $qrDataUri = 'data:image/png;base64,' . base64_encode($qrResult->getString());
            } catch (\Throwable $qrPngError) {
                log_message('warning', 'QR PNG failed (GD not available?), trying SVG writer: ' . $qrPngError->getMessage());
                try {
                    $builder = new \Endroid\QrCode\Builder\Builder(
                        data: $verificationUrl,
                        size: 220,
                        margin: 6,
                        writer: new \Endroid\QrCode\Writer\SvgWriter()
                    );
                    $qrResult = $builder->build();
                    $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrResult->getString());
                } catch (\Throwable $qrSvgError) {
                    log_message('warning', 'QR SVG also failed, certificate will have no QR: ' . $qrSvgError->getMessage());
                }
            }

            $viewData = [
                'certificate_number' => (string) ($certificate['certificate_number'] ?? ''),
                'user_name' => (string) ($user['name'] ?? 'Learner'),
                'course_name' => (string) ($course['title'] ?? 'Course'),
                'issued_at' => (string) ($certificate['issued_at'] ?? date('Y-m-d H:i:s')),
                'qr_data_uri' => $qrDataUri,
                'qr_code' => $qrDataUri, // For backwards compatibility
            ];

            // Render view
            $html = view('certificate_template', $viewData);

            // Configure DomPDF options - matching repository
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('chroot', FCPATH);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $relativeDir = 'certificates';
            $absoluteDir = WRITEPATH . $relativeDir;
            if (!is_dir($absoluteDir)) {
                mkdir($absoluteDir, 0775, true);
            }

            $filename = 'certificate-' . $certificate['id'] . '-' . strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $certificate['certificate_number'] ?? 'cert')) . '.pdf';
            $relativePath = $relativeDir . '/' . $filename;
            $absolutePath = WRITEPATH . $relativePath;

            file_put_contents($absolutePath, $dompdf->output());

            // Update certificate record
            $this->certificateModel->update($certificateId, [
                'certificate_path' => $relativePath,
                'user_name' => $viewData['user_name'],
                'course_name' => $viewData['course_name'],
                'qr_code' => $verificationUrl,
            ]);

            return $relativePath;
        } catch (\Throwable $e) {
            log_message('error', 'Certificate PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }
}