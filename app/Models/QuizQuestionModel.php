<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizQuestionModel extends Model
{
    protected $table = 'quiz_questions';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'quiz_id',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'marks',
        'position'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $useSoftDeletes = false;
    protected $deletedField  = 'deleted_at';
}
