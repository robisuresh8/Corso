<?php
namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Throwable;

class Health extends ResourceController
{
    protected $format = 'json';

    private function cors(): void
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    }

    public function index(): ResponseInterface
    {
        $this->cors();
        return $this->respond(['status' => 'ok', 'app' => 'ci4', 'time' => date('c')]);
    }

    public function cfg(): ResponseInterface
    {
        $this->cors();
        $cfg = config('Database');
        $d = $cfg->default ?? [];
        return $this->respond([
            'host'   => $d['hostname'] ?? null,
            'user'   => $d['username'] ?? null,
            'db'     => $d['database'] ?? null,
            'driver' => $d['DBDriver'] ?? null,
            'port'   => $d['port'] ?? null,
        ]);
    }

    public function db(): ResponseInterface
    {
        $this->cors();
        try {
            $db = Database::connect(); // uses app/Config/Database.php
            $q  = $db->query('SELECT 1 AS ok');
            $row = $q->getRowArray();
            return $this->respond(['db' => (isset($row['ok']) && (int)$row['ok'] === 1) ? 'ok' : 'fail']);
        } catch (Throwable $e) {
            return $this->respond(['db' => 'fail', 'error' => $e->getMessage()], 500);
        }
    }
}