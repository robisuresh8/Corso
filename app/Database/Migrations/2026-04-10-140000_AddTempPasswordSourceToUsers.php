<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTempPasswordSourceToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('temp_password_source', 'users')) {
            $this->forge->addColumn('users', [
                'temp_password_source' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => true,
                    'default'    => null,
                    'comment'    => 'purchase | forgot — controls which UI may use temp password',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('temp_password_source', 'users')) {
            $this->forge->dropColumn('users', 'temp_password_source');
        }
    }
}
