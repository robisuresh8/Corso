<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCertificateExtraFields extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'certificate_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'course_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'course_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'course' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
                'type' => 'DATETIME',
                'null' => true,
            ],
            'certificate_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'qr_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'download_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'revoked'],
                'default'    => 'active',
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'course_id']);
        $this->forge->createTable('certificates_new', true);

        // Migrate data from old table if it exists
        $db = \Config\Database::connect();
        if ($db->tableExists('certificates')) {
            $oldFields = $db->getFieldNames('certificates');
            
            $selectFields = ['id', 'certificate_number', 'user_id', 'course_id', 'issued_at', 'certificate_path', 'status', 'revoked_at'];
            foreach ($selectFields as $field) {
                if (!in_array($field, $oldFields)) {
                    unset($selectFields[array_search($field, $selectFields)]);
                }
            }
            
            if (!empty($selectFields)) {
                $query = $db->table('certificates')->select(implode(',', $selectFields))->get();
                foreach ($query->getResultArray() as $row) {
                    $this->db->table('certificates_new')->insert($row);
                }
            }
            
            $this->forge->dropTable('certificates');
        }

        $this->forge->renameTable('certificates_new', 'certificates');
    }

    public function down()
    {
        $this->forge->dropTable('certificates');
    }
}
