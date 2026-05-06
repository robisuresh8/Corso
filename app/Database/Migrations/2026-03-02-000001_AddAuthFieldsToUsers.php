<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuthFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'role'                => ['type' => 'ENUM', 'constraint' => ['admin', 'student', 'instructor'], 'null' => true, 'default' => 'student'],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'active'],
            'email_verified'      => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'email_verified_at'   => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'verification_token'  => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'default' => null],
            'last_login_at'       => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'reset_token'         => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'default' => null],
            'reset_expires'       => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ];
        foreach ($fields as $name => $def) {
            if (!$this->db->fieldExists($name, 'users')) {
                $this->forge->addColumn('users', [$name => $def]);
            }
        }
    }

    public function down()
    {
        $fields = ['role', 'status', 'email_verified', 'email_verified_at', 'verification_token', 'last_login_at', 'reset_token', 'reset_expires'];
        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $this->forge->dropColumn('users', $field);
            }
        }
    }
}
