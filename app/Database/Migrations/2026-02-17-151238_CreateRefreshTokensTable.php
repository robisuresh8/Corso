<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefreshTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'user_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'token'=>['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'expires_at'=>['type'=>'DATETIME','null'=>false],
            'created_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP']
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('refresh_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('refresh_tokens', true);
    }
}
