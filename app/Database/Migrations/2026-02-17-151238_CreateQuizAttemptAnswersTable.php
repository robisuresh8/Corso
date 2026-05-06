<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizAttemptAnswersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'attempt_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'question_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'selected_option'=>['type'=>'ENUM','constraint'=>['A','B','C','D'],'null'=>true],
            'is_correct'=>['type'=>'TINYINT','constraint'=>1,'null'=>false,'default'=>0],
            'marks_awarded'=>['type'=>'INT','constraint'=>11,'null'=>false,'default'=>0],
            'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP']
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('quiz_attempt_answers', true);
    }

    public function down()
    {
        $this->forge->dropTable('quiz_attempt_answers', true);
    }
}
