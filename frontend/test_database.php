<?php
/**
 * Simple Database Connection Test for Kanha Ecommerce
 * Run this file to test if Laravel can connect to MySQL
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "========================================\n";
echo "Database Connection Test\n";
echo "========================================\n\n";

// Display current configuration
echo "Current Database Configuration:\n";
echo "DB_CONNECTION: " . ($_ENV['DB_CONNECTION'] ?? 'not set') . "\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? 'not set') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n";
echo "DB_PASSWORD: " . (empty($_ENV['DB_PASSWORD']) ? '(empty)' : '(set)') . "\n\n";

// Test MySQL connection
try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'kanha_ecommerce';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ MySQL Connection: SUCCESS\n";
    echo "Connected to database: {$database}\n\n";

    // Test if tables exist
    $tables = [
        'users', 'products', 'categories', 'cart', 'orders', 'reviews'
    ];

    echo "Checking for required tables:\n";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Table '{$table}': EXISTS\n";
            } else {
                echo "❌ Table '{$table}': MISSING\n";
            }
        } catch (Exception $e) {
            echo "❌ Table '{$table}': ERROR - " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // Test data insertion
    echo "Testing data insertion:\n";
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $testEmail = 'test_' . time() . '@example.com';
        $stmt->execute(['Test User', $testEmail, password_hash('password', PASSWORD_DEFAULT)]);
        echo "✅ Data insertion: SUCCESS\n";
        
        // Clean up test data
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
        echo "✅ Data cleanup: SUCCESS\n";
    } catch (Exception $e) {
        echo "❌ Data insertion: FAILED - " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ MySQL Connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Troubleshooting steps:\n";
    echo "1. Make sure XAMPP MySQL service is running\n";
    echo "2. Create database 'kanha_ecommerce' in phpMyAdmin\n";
    echo "3. Check your .env file configuration\n";
    echo "4. Run: php artisan migrate\n";
}

echo "\n========================================\n";
echo "Test Complete\n";
echo "========================================\n";
?>
