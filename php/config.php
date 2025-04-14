<?php
// Database Configuration
$servername = "db"; // Use container name in Docker
$username = "smartuser"; // Using MySQL user from docker-compose
$password = "smartpass"; // Using MySQL password from docker-compose
$dbname = "smart_india_mapping";

// Create connection
function getDBConnection() {
    global $servername, $username, $password, $dbname;
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Session configuration
session_start();

// Constants
define('RESOURCES_TABLE_PREFIX', 'resources_');
define('USER_TABLE', 'users');
define('ACTIVITY_LOG_TABLE', 'activity_log');
define('REGIONS_TABLE', 'regions');

// Resource types
$resourceTypes = ['schools', 'hospitals', 'water', 'electricity'];

// Helper function to validate if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to log activity
function logActivity($action, $userId = null) {
    $conn = getDBConnection();
    
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    $action = $conn->real_escape_string($action);
    $userId = $userId !== null ? intval($userId) : 'NULL';
    
    $query = "INSERT INTO " . ACTIVITY_LOG_TABLE . " (user_id, action, timestamp) VALUES ($userId, '$action', NOW())";
    $conn->query($query);
    
    $conn->close();
}

// Helper function to sanitize input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Helper function to get resource table name
function getResourceTableName($resourceType) {
    return RESOURCES_TABLE_PREFIX . $resourceType;
}

// Helper function to validate resource type
function isValidResourceType($type) {
    global $resourceTypes;
    return in_array($type, $resourceTypes);
}
?> 