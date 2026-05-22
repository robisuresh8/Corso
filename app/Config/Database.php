<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath    = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'          => '',
        'hostname'     => '127.0.0.1',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'corso',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3307,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Read directly from $_SERVER (Apache PassEnv sets them here)
        $hostname = $_SERVER['DB_HOSTNAME'] ?? getenv('DB_HOSTNAME') ?: null;

        if ($hostname) {
            $this->default['hostname'] = $hostname;
            $this->default['username'] = $_SERVER['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: $this->default['username'];
            $this->default['password'] = $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: $this->default['password'];
            $this->default['database'] = $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: $this->default['database'];
            $this->default['port']     = (int)($_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: $this->default['port']);
            $this->default['DBDriver'] = $_SERVER['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: $this->default['DBDriver'];
            // Aiven SSL required
            $this->default['encrypt']  = true;
            $this->default['strictOn'] = false;
            $this->default['DBDebug']  = false;
        }

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
