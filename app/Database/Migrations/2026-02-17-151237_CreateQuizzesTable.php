<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizzesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'course_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => false
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true
            ],
            'total_marks' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'passing_marks' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'negative_marks' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
            ],
            'negative_marking' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Enable negative marking (0/1)',
            ],
            'max_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 3,
                'comment' => 'Maximum attempts allowed',
            ],
            'duration' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 30,
                'comment' => 'Quiz duration in minutes',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('quizzes', true);
    }

    public function down()
    {
        $this->forge->dropTable('quizzes', true);
    }
}
