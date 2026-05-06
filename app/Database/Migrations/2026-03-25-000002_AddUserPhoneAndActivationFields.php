<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserPhoneAndActivationFields extends Migration
{
    public function up()
    {
        // phone: marketing-only, not used for login
        if (!$this->db->fieldExists('phone', 'users')) {
            $this->forge->addColumn('users', [
                'phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'default' => null,
                ],
            ]);
        }

        // activation_token: set during pre-registration, consumed on payment completion
        if (!$this->db->fieldExists('activation_token', 'users')) {
            $this->forge->addColumn('users', [
                'activation_token' => [
                    'type' => 'VARCHAR',
                    'constraint' => 64,
                    'null' => true,
                    'default' => null,
                ],
            ]);
        }

        // force_password_change: set to 1 when temporary password is generated
        if (!$this->db->fieldExists('force_password_change', 'users')) {
            $this->forge->addColumn('users', [
                'force_password_change' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => 0,
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = ['phone', 'activation_token', 'force_password_change'];
        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $this->forge->dropColumn('users', $field);
            }
        }
    }
}

