<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Throwable;

/**
 * Database connection test (CodeIgniter).
 * Run: php spark db:test
 */
class DbTest extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:test';
    protected $description = 'Test database connection and list tables.';
    protected $usage       = 'db:test';

    public function run(array $params): void
    {
        CLI::write('=== Database Connection Test ===', 'green');
        CLI::newLine();

        $config = config('Database');
        $default = $config->default ?? [];
        $hostname = $default['hostname'] ?? '127.0.0.1';
        $database = $default['database'] ?? 'corso';
        $username = $default['username'] ?? 'root';
        $password = $default['password'] ?? '';
        $port = (int) ($default['port'] ?? 3306);
        $driver = $default['DBDriver'] ?? 'MySQLi';

        CLI::write('Configuration:', 'yellow');
        CLI::write("  Hostname: {$hostname}");
        CLI::write("  Database: {$database}");
        CLI::write("  Username: {$username}");
        CLI::write('  Password: ' . (empty($password) ? '(empty)' : '***'));
        CLI::write("  Port: {$port}");
        CLI::write("  Driver: {$driver}");
        CLI::newLine();

        if (strtolower($driver) !== 'mysqli' && strtolower($driver) !== 'mysql') {
            CLI::write('Test 1: Driver', 'yellow');
            CLI::error('  Only MySQLi/MySQL driver is fully supported by this command.');
            return;
        }

        CLI::write('Test 1: MySQL extension', 'yellow');
        if (extension_loaded('mysqli')) {
            CLI::write('  ✓ mysqli extension is loaded', 'green');
        } else {
            CLI::error('  ✗ mysqli extension is NOT loaded');
            return;
        }

        CLI::newLine();
        CLI::write('Test 2: CodeIgniter database connection', 'yellow');
        try {
            $db = Database::connect();
            $db->query('SELECT 1 AS ok');
            CLI::write('  ✓ Successfully connected and ran SELECT 1', 'green');
        } catch (Throwable $e) {
            CLI::error('  ✗ Connection failed: ' . $e->getMessage());
            $this->suggestFix($e, $port, $hostname);
            return;
        }

        CLI::newLine();
        CLI::write('Test 3: Tables', 'yellow');
        try {
            $tables = $db->listTables();
            if (empty($tables)) {
                CLI::write('  ⚠ No tables found', 'yellow');
                CLI::write('  → Run: php spark migrate');
            } else {
                CLI::write('  ✓ Found ' . count($tables) . ' table(s):', 'green');
                foreach ($tables as $table) {
                    CLI::write('    - ' . $table);
                }
            }
        } catch (Throwable $e) {
            CLI::error('  ✗ ' . $e->getMessage());
        }

        CLI::newLine();
        CLI::write('=== Test complete ===', 'green');
    }

    private function suggestFix(Throwable $e, int $port, string $hostname): void
    {
        $msg = $e->getMessage();
        CLI::newLine();
        if (strpos($msg, 'refused') !== false || strpos($msg, '2002') !== false || strpos($msg, '2003') !== false) {
            CLI::write('  → Start MySQL in XAMPP (Start next to MySQL in Control Panel), then run again.');
        } elseif (strpos($msg, '1045') !== false || strpos($msg, 'Access denied') !== false) {
            CLI::write('  → Check .env: database.default.username and database.default.password');
        } else {
            CLI::write("  → Check MySQL is running, port {$port}, hostname {$hostname}");
        }
    }
}
