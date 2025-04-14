<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get resource type parameter
$resourceType = isset($_GET['resource_type']) ? sanitizeInput($_GET['resource_type']) : '';

// Get database connection
$conn = getDBConnection();

if (!empty($resourceType) && isValidResourceType($resourceType)) {
    // Get regions that have resources of the specified type
    $tableName = getResourceTableName($resourceType);
    $query = "SELECT DISTINCT state FROM $tableName ORDER BY state";
} else {
    // Get all regions from the regions table
    $query = "SELECT name FROM " . REGIONS_TABLE . " ORDER BY name";
}

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['error' => 'Database query error: ' . $conn->error]);
    $conn->close();
    exit();
}

// Fetch regions
$regions = [];
while ($row = $result->fetch_assoc()) {
    if (isset($row['state'])) {
        $regions[] = $row['state'];
    } else {
        $regions[] = $row['name'];
    }
}

// Return JSON response
echo json_encode($regions);

$conn->close();
?> 