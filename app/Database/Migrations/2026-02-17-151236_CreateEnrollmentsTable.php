<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnrollmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'user_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'course_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'enrolled_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
            'status'=>['type'=>'ENUM','constraint'=>['active','inactive'],'default'=>'active']
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('enrollments', true);
    }

    public function down()
    {
        $this->forge->dropTable('enrollments', true);
    }
}
