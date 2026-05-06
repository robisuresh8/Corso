<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'name'=>['type'=>'VARCHAR','constraint'=>100,'null'=>false],
            'email'=>['type'=>'VARCHAR','constraint'=>150,'null'=>false],
            'password'=>['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'role'=>['type'=>'ENUM','constraint'=>['admin','student','instructor'],'default'=>'student'],
            'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
            'updated_at'=>['type'=>'DATETIME','null'=>true,'default'=>null]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}
