<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolePermissionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'role_slug'       => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => false],
            'permission_slug' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['role_slug', 'permission_slug']);
        $this->forge->createTable('role_permissions', true);
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions', true);
    }
}
