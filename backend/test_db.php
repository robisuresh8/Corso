<?php
/**
 * Database Connection Test Script
 * Run this file directly: php test_db.php
 */

echo "=== Database Connection Test ===\n\n";

// Simple .env parser
function parseEnv($file) {
    $config = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Remove quotes if present
                $value = trim($value, '"\'');
                $config[$key] = $value;
            }
        }
    }
    return $config;
}

$env = parseEnv(__DIR__ . '/.env');

// Get database config from .env
$hostname = $env['database.default.hostname'] ?? '127.0.0.1';
$database = $env['database.default.database'] ?? 'corso';
$username = $env['database.default.username'] ?? 'root';
$password = $env['database.default.password'] ?? '';
$port = (int)($env['database.default.port'] ?? 3306);

echo "Configuration:\n";
echo "  Hostname: {$hostname}\n";
echo "  Database: {$database}\n";
echo "  Username: {$username}\n";
echo "  Password: " . (empty($password) ? '(empty)' : '***') . "\n";
echo "  Port: {$port}\n\n";

// Test 1: Check if MySQL extension is loaded
echo "Test 1: MySQL Extension Check\n";
if (extension_loaded('mysqli')) {
    echo "  ✓ mysqli extension is loaded\n";
} else {
    echo "  ✗ mysqli extension is NOT loaded\n";
    exit(1);
}

// Test 2: Try to connect to MySQL server
echo "\nTest 2: MySQL Server Connection\n";
try {
    $mysqli = @new mysqli($hostname, $username, $password, '', $port);
    
    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error, $mysqli->connect_errno);
    }
    
    echo "  ✓ Successfully connected to MySQL server\n";
    echo "  Server Version: " . $mysqli->server_info . "\n";
} catch (Exception $e) {
    echo "  ✗ Connection failed: " . $e->getMessage() . "\n";
    echo "  Error Code: " . $e->getCode() . "\n\n";
    
    $errno = $e->getCode();
    if ($errno == 2002 || $errno == 2003 || strpos($e->getMessage(), 'actively refused') !== false) {
        echo "  ⚠ MySQL server is NOT running!\n\n";
        echo "  → SOLUTION: Start MySQL in XAMPP:\n";
        echo "    1. Open XAMPP Control Panel\n";
        echo "    2. Click 'Start' button next to MySQL\n";
        echo "    3. Wait for MySQL to start (status should turn green)\n";
        echo "    4. Run this test again\n\n";
    } elseif ($errno == 1045) {
        echo "  → Suggestion: Invalid username or password.\n";
        echo "    Check your .env file database credentials.\n";
    } else {
        echo "  → Check your MySQL configuration:\n";
        echo "    - Is MySQL service running?\n";
        echo "    - Is port {$port} correct?\n";
        echo "    - Is hostname '{$hostname}' correct?\n";
    }
    exit(1);
}

// Test 3: Check if database exists
echo "\nTest 3: Database Existence Check\n";
$dbExists = false;
$result = $mysqli->query("SHOW DATABASES LIKE '{$database}'");
if ($result && $result->num_rows > 0) {
    echo "  ✓ Database '{$database}' exists\n";
    $dbExists = true;
} else {
    echo "  ✗ Database '{$database}' does NOT exist\n";
    echo "  → Suggestion: Create the database:\n";
    echo "    CREATE DATABASE {$database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
}

// Test 4: Try to select the database
if ($dbExists) {
    echo "\nTest 4: Database Selection\n";
    if ($mysqli->select_db($database)) {
        echo "  ✓ Successfully selected database '{$database}'\n";
        
        // Test 5: Check if tables exist
        echo "\nTest 5: Tables Check\n";
        $result = $mysqli->query("SHOW TABLES");
        if ($result) {
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            if (empty($tables)) {
                echo "  ⚠ No tables found in database\n";
                echo "  → Suggestion: Run migrations:\n";
                echo "    php spark migrate\n";
            } else {
                echo "  ✓ Found " . count($tables) . " table(s):\n";
                foreach ($tables as $table) {
                    echo "    - {$table}\n";
                }
            }
        }
    } else {
        echo "  ✗ Failed to select database '{$database}'\n";
        echo "  Error: " . $mysqli->error . "\n";
    }
}

$mysqli->close();

echo "\n=== Test Complete ===\n";
