<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class RemoveNegativeAndAttemptsFromQuizzes extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();
        $db = Database::connect();

        // Get list of columns from the table
        $columns = $db->getFieldNames('quizzes');

        // Columns to remove
        $fieldsToRemove = ['negative_marking', 'negative_marks', 'max_attempts'];

        foreach ($fieldsToRemove as $field) {
            if (in_array($field, $columns)) {
                $forge->dropColumn('quizzes', $field);
            }
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();

        // Add columns back in case of rollback
        $forge->addColumn('quizzes', [
            'negative_marking' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'passing_marks',
            ],
            'negative_marks' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'after' => 'negative_marking',
            ],
            'max_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 3,
                'after' => 'negative_marks',
            ],
        ]);
    }
}
