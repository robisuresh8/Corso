<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'user_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'course_id'=>['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>false],
            'amount'=>['type'=>'DECIMAL','constraint'=>'10,2','null'=>false],
            'payment_method'=>['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'paid_at'=>['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP']
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payments', true);
    }

    public function down()
    {
        $this->forge->dropTable('payments', true);
    }
}
