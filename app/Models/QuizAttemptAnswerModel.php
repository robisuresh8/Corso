<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizAttemptAnswerModel extends Model
{
    protected $table      = 'quiz_attempt_answers';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'attempt_id',
        'question_id',
        'selected_option',
        'is_correct',
        'marks_awarded'
    ];

    protected $useTimestamps = true; // ← no updated_at in table
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // Disables updated_at specifically
}