<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuizAttemptsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'quiz_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'user_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'score'=>['type'=>'INT','constraint'=>11,'null'=>false,'default'=>0],
            'passed'=>['type'=>'TINYINT','constraint'=>1,'null'=>false,'default'=>0],
            'attempted_at'=>['type'=>'DATETIME','null'=>true,'default'=>'CURRENT_TIMESTAMP'],
            'best_attempt'=>['type'=>'TINYINT','constraint'=>1,'null'=>false,'default'=>0],
            'started_at'=>['type'=>'DATETIME','null'=>true,'default'=>null],
            'total_questions'=>['type'=>'INT','constraint'=>11,'null'=>false,'default'=>0],
            'completed_at'=>['type'=>'DATETIME','null'=>true,'default'=>null],
            'time_taken'=>['type'=>'INT','constraint'=>11,'null'=>true,'default'=>0],
            'ip_address'=>['type'=>'VARCHAR','constraint'=>45,'null'=>true]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('quiz_attempts', true);
    }

    public function down()
    {
        $this->forge->dropTable('quiz_attempts', true);
    }
}
