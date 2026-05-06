<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForgotPasswordExpiresToUsers extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('forgot_password_expires_at', 'users')) {
            $this->forge->addColumn('users', [
                'forgot_password_expires_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('forgot_password_expires_at', 'users')) {
            $this->forge->dropColumn('users', 'forgot_password_expires_at');
        }
    }
}
