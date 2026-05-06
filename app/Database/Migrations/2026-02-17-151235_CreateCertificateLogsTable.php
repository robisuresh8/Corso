<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateCertificateLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'certificate_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'action'=>['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'performed_at'=>['type'=>'DATETIME','null'=>false,'default'=>new RawSql('CURRENT_TIMESTAMP')],
            'performed_by'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('certificate_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('certificate_logs', true);
    }
}
