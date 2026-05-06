<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNegativeMarkingToQuizzes extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('quizzes')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('negative_marking', 'quizzes')) {
            $fields['negative_marking'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'passing_marks',
                'comment'    => 'Enable negative marking (0/1)',
            ];
        }
        if (!$this->db->fieldExists('negative_marks', 'quizzes')) {
            $fields['negative_marks'] = [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'after'      => 'negative_marking',
                'comment'    => 'Negative marks per wrong answer',
            ];
        }
        if (!$this->db->fieldExists('max_attempts', 'quizzes')) {
            $fields['max_attempts'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 3,
                'after'      => 'negative_marks',
                'comment'    => 'Maximum attempts allowed',
            ];
        }
        if (!$this->db->fieldExists('duration', 'quizzes')) {
            $fields['duration'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 30,
                'after'      => 'max_attempts',
                'comment'    => 'Quiz duration in minutes',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('quizzes', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('quizzes')) {
            return;
        }

        $drop = [];
        foreach (['negative_marking', 'negative_marks', 'max_attempts', 'duration'] as $column) {
            if ($this->db->fieldExists($column, 'quizzes')) {
                $drop[] = $column;
            }
        }
        if ($drop !== []) {
            $this->forge->dropColumn('quizzes', $drop);
        }
    }
}
