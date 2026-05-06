<?php
namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'category_id',
        'created_by',
        'title',
        'slug',
        'description',
        'thumbnail',
        'price',
        'level',
        'status',
        'quiz_duration_minutes'
    ];
    protected $useTimestamps = true; // handles created_at & updated_at
    protected $useSoftDeletes = false; // ← change to false
    protected $deletedField = 'deleted_at';
}
