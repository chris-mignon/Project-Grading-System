<?php
// Test bootstrap file

// Define test environment
define('TEST_ENV', true);

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Create test database configuration
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';

// Start session for auth tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Function to initialize test database
function initializeTestDatabase() {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    // Insert test data
    $testData = file_get_contents(__DIR__ . '/test_data.sql');
    $testStatements = array_filter(array_map('trim', explode(';', $testData)));
    
    foreach ($testStatements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    return $db;
}