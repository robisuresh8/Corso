<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'body' => ['type' => 'TEXT', 'null' => true],
            'target_roles' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], // comma-separated or 'all'
            'starts_at' => ['type' => 'DATETIME', 'null' => true],
            'ends_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('announcements', true);
    }

    public function down()
    {
        $this->forge->dropTable('announcements', true);
    }
}
