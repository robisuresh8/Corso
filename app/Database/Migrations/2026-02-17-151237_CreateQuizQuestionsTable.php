<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizQuestionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'quiz_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'question'=>['type'=>'TEXT','null'=>false],
            'option_a'=>['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'option_b'=>['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'option_c'=>['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'option_d'=>['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'correct_option'=>['type'=>'ENUM','constraint'=>['A','B','C','D'],'null'=>false],
            'marks'=>['type'=>'INT','constraint'=>11,'null'=>false],
            'position'=>['type'=>'INT','constraint'=>11,'null'=>false],
            'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
            'updated_at'=>['type'=>'DATETIME','null'=>true,'default'=>null],
            'deleted_at'=>['type'=>'DATETIME','null'=>true,'default'=>null]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('quiz_questions', true);
    }

    public function down()
    {
        $this->forge->dropTable('quiz_questions', true);
    }
}
