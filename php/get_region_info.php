<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get region parameter
$region = isset($_GET['region']) ? sanitizeInput($_GET['region']) : '';

if (empty($region)) {
    echo json_encode(['error' => 'Region parameter is required']);
    exit();
}

// Get database connection
$conn = getDBConnection();

// Escape for SQL
$region = $conn->real_escape_string($region);

// Get region data
$query = "SELECT id, name, population, latitude, longitude FROM " . REGIONS_TABLE . " WHERE name = '$region'";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    echo json_encode([
        'name' => $region,
        'population' => 0,
        'schools' => 0,
        'hospitals' => 0,
        'water' => 0,
        'electricity' => 0
    ]);
    $conn->close();
    exit();
}

$regionData = $result->fetch_assoc();

// Get resource counts for this region
$resourceCounts = [
    'schools' => 0,
    'hospitals' => 0,
    'water' => 0,
    'electricity' => 0
];

foreach ($resourceCounts as $type => $count) {
    $tableName = getResourceTableName($type);
    $countQuery = "SELECT COUNT(*) as count FROM $tableName WHERE state = '$region'";
    $countResult = $conn->query($countQuery);
    
    if ($countResult && $countRow = $countResult->fetch_assoc()) {
        $resourceCounts[$type] = intval($countRow['count']);
    }
}

// Combine region data with resource counts
$response = array_merge($regionData, $resourceCounts);

// Return JSON response
echo json_encode($response);

$conn->close();
?> 