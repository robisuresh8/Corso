<?php
namespace App\Models;

use CodeIgniter\Model;

class QuizModel extends Model
{
    protected $table = 'quizzes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'course_id',
        'title',
        'slug',
        'total_marks',
        'passing_marks',
        'duration'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $useSoftDeletes = false;

    protected $beforeInsert = ['generateSlug'];
    protected $beforeUpdate = ['generateSlug'];

    protected function generateSlug(array $data)
    {
        if (isset($data['data']['title'])) {
            $slug = url_title($data['data']['title'], '-', true);
            $originalSlug = $slug;
            $counter = 1;

            while ($this->where('slug', $slug)->first()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $data['data']['slug'] = $slug;
        }
        return $data;
    }
}