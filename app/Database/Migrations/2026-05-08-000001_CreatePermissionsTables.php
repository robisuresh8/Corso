<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissionsTables extends Migration
{
    public function up()
    {
        // ---------------------------------------------------------
        // permissions table
        // ---------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'student'],
                'default'    => 'admin',
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('permissions', true);  // true = IF NOT EXISTS

        // ---------------------------------------------------------
        // role_permissions table
        // ---------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['student', 'admin', 'superadmin'],
                'null'       => false,
            ],
            'permission_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions', true);  // true = IF NOT EXISTS

        // ---------------------------------------------------------
        // user_permissions table
        // ---------------------------------------------------------
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'permission_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id',       'users',       'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_permissions', true);  // true = IF NOT EXISTS

        // ---------------------------------------------------------
        // Seed default admin permissions
        // ---------------------------------------------------------
        $now = date('Y-m-d H:i:s');
        $db  = \Config\Database::connect();

        $adminPermissions = [
            ['name' => 'Manage Courses',      'slug' => 'manage_courses',      'type' => 'admin', 'description' => 'Create, edit, delete courses'],
            ['name' => 'Manage Quizzes',       'slug' => 'manage_quizzes',       'type' => 'admin', 'description' => 'Create, edit, delete quizzes'],
            ['name' => 'Manage Users',         'slug' => 'manage_users',         'type' => 'admin', 'description' => 'View and manage student accounts'],
            ['name' => 'Manage Certificates',  'slug' => 'manage_certificates',  'type' => 'admin', 'description' => 'Issue, revoke, reissue certificates'],
            ['name' => 'Manage Payments',      'slug' => 'manage_payments',      'type' => 'admin', 'description' => 'View payment records'],
            ['name' => 'View Analytics',       'slug' => 'view_analytics',       'type' => 'admin', 'description' => 'Access dashboard analytics'],
            ['name' => 'Manage Staff',         'slug' => 'manage_staff',         'type' => 'admin', 'description' => 'Add or edit admin staff accounts'],
            ['name' => 'Delete Staff',         'slug' => 'delete_staff',         'type' => 'admin', 'description' => 'Remove admin staff accounts'],
        ];

        $studentPermissions = [
            ['name' => 'Enroll in Courses',  'slug' => 'enroll_courses',   'type' => 'student', 'description' => 'Enroll in available courses'],
            ['name' => 'Take Quizzes',        'slug' => 'take_quizzes',     'type' => 'student', 'description' => 'Attempt quizzes'],
            ['name' => 'View Certificates',   'slug' => 'view_certificates','type' => 'student', 'description' => 'View and download own certificates'],
            ['name' => 'View Payment History','slug' => 'view_payments',    'type' => 'student', 'description' => 'View own payment history'],
        ];

        foreach (array_merge($adminPermissions, $studentPermissions) as $perm) {
            $db->table('permissions')->insert(array_merge($perm, ['created_at' => $now, 'updated_at' => $now]));
        }
    }

    public function down()
    {
        $this->forge->dropTable('user_permissions',  true);
        $this->forge->dropTable('role_permissions',  true);
        $this->forge->dropTable('permissions',       true);
    }
}