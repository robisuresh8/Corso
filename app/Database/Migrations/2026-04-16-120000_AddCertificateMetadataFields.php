<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCertificateMetadataFields extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('certificates')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('user_name', 'certificates')) {
            $fields['user_name'] = [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ];
        }
        if (!$this->db->fieldExists('course_name', 'certificates')) {
            $fields['course_name'] = [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ];
        }
        if (!$this->db->fieldExists('download_count', 'certificates')) {
            $fields['download_count'] = [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ];
        }
        if (!$this->db->fieldExists('qr_code', 'certificates')) {
            $fields['qr_code'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('certificates', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('certificates')) {
            return;
        }

        $dropColumns = [];
        foreach (['user_name', 'course_name', 'download_count', 'qr_code'] as $column) {
            if ($this->db->fieldExists($column, 'certificates')) {
                $dropColumns[] = $column;
            }
        }

        if ($dropColumns !== []) {
            $this->forge->dropColumn('certificates', $dropColumns);
        }
    }
}
