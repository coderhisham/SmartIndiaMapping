<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

// Get database connection
$conn = getDBConnection();

// Get counts for each resource type
$resourceCounts = [
    'schools' => 0,
    'hospitals' => 0,
    'water' => 0,
    'electricity' => 0,
    'total' => 0
];

foreach ($resourceCounts as $type => &$count) {
    if ($type === 'total') continue;
    
    $tableName = getResourceTableName($type);
    $query = "SELECT COUNT(*) as count FROM $tableName";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        $count = intval($row['count']);
        $resourceCounts['total'] += $count;
    }
}

// Get region count
$query = "SELECT COUNT(*) as count FROM " . REGIONS_TABLE;
$result = $conn->query($query);
$regionCount = 0;

if ($result && $row = $result->fetch_assoc()) {
    $regionCount = intval($row['count']);
}

// Add region count to response
$response = array_merge($resourceCounts, ['regions' => $regionCount]);

// Return JSON response
echo json_encode($response);

$conn->close();
?> 