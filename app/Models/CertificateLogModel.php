<?php
namespace App\Models;

use CodeIgniter\Model;

class CertificateLogModel extends Model
{
    protected $table = 'certificate_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'certificate_id','user_id','score','issued_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'issued_at';
}
