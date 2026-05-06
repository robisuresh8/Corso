<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificateModel extends Model
{
    protected $table = 'certificates';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id',
        'certificate_number',
        'user_id',
        'course_id',
        'user_name',
        'course_name',
        'name',
        'course',
        'score',
        'total',
        'issued_at',
        'certificate_path',
        'qr_code',
        'download_count',
        'status',
        'revoked_at',
    ];

    protected $useTimestamps = false;
    protected $returnType = 'array';
}
