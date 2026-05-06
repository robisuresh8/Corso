<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCertificates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'course' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'score' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 10,
            ],
            'issued_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('certificates', true);
    }

    public function down()
    {
        $this->forge->dropTable('certificates', true);
    }
}
