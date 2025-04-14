<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get parameters
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
$region = isset($_GET['region']) ? sanitizeInput($_GET['region']) : '';

// Validate resource type
if (!isValidResourceType($type)) {
    echo json_encode(['error' => 'Invalid resource type']);
    exit();
}

// Get database connection
$conn = getDBConnection();

// Build query
$tableName = getResourceTableName($type);
$query = "SELECT * FROM $tableName";

if (!empty($region)) {
    $region = $conn->real_escape_string($region);
    $query .= " WHERE state = '$region'";
}

// Execute query
$result = $conn->query($query);

if (!$result) {
    echo json_encode(['error' => 'Database query error: ' . $conn->error]);
    $conn->close();
    exit();
}

// Fetch data
$resources = [];
while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}

// Return JSON response
echo json_encode($resources);

$conn->close();
?> 