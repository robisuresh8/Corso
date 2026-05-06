<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizAttemptModel extends Model
{
    protected $table      = 'quiz_attempts';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'quiz_id',
        'user_id',
        'score',
        'passed',
        'best_attempt',
        'started_at',
        'completed_at',
        'total_questions',
        'time_taken',
        'ip_address',
        'result',
        'obtained_marks',
        'total_marks',
        'attempted_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'attempted_at';
    protected $useSoftDeletes = false;
}