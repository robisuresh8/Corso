<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'name' => ['type'=>'VARCHAR','constraint'=>100,'null'=>false],
            'slug' => ['type'=>'VARCHAR','constraint'=>100,'null'=>false],
            'description' => ['type'=>'TEXT','null'=>true],
            'created_at' => ['type'=>'DATETIME','null'=>false,'default'=> 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type'=>'DATETIME','null'=>true,'default'=>null],
            'deleted_at' => ['type'=>'DATETIME','null'=>true,'default'=>null]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('categories', true);
    }

    public function down()
    {
        $this->forge->dropTable('categories', true);
    }
}
