<?php
namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'course_id',
        'enrollment_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'paid_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true; // will auto-fill created_at & updated_at
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
