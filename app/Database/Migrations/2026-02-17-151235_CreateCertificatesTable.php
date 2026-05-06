<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCertificatesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'certificate_number' => ['type'=>'VARCHAR','constraint'=>100,'null'=>false],
            'user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'course_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'issued_at' => ['type'=>'DATETIME','null'=>true,'default'=>'CURRENT_TIMESTAMP'],
            'certificate_path' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'status' => ['type'=>'ENUM','constraint'=>['active','revoked'],'default'=>'active'],
            'revoked_at' => ['type'=>'DATETIME','null'=>true,'default'=>null]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('certificates', true);
    }

    public function down()
    {
        $this->forge->dropTable('certificates', true);
    }
}
