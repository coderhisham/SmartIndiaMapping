<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access', 'success' => false]);
    exit();
}

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method', 'success' => false]);
    exit();
}

// Get parameters
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = isset($_POST['type']) ? sanitizeInput($_POST['type']) : '';

// Validate parameters
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID', 'success' => false]);
    exit();
}

if (!isValidResourceType($type)) {
    echo json_encode(['error' => 'Invalid resource type', 'success' => false]);
    exit();
}

// Get database connection
$conn = getDBConnection();

// Get table name
$tableName = getResourceTableName($type);

// Delete the resource
$stmt = $conn->prepare("DELETE FROM $tableName WHERE id = ?");
$stmt->bind_param('i', $id);
$result = $stmt->execute();

if ($result) {
    // Log the activity
    logActivity("Deleted $type resource with ID: $id");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to delete resource: ' . $conn->error, 'success' => false]);
}

$stmt->close();
$conn->close();
?> 