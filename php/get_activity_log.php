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

// Get recent activity logs, limit to 10 most recent
$query = "SELECT a.id, a.action, a.timestamp, u.username 
          FROM " . ACTIVITY_LOG_TABLE . " a
          LEFT JOIN " . USER_TABLE . " u ON a.user_id = u.id
          ORDER BY a.timestamp DESC
          LIMIT 10";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['error' => 'Database query error: ' . $conn->error]);
    $conn->close();
    exit();
}

// Fetch data
$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = [
        'id' => $row['id'],
        'action' => $row['action'],
        'username' => $row['username'] ?: 'System',
        'timestamp' => $row['timestamp']
    ];
}

// Return JSON response
echo json_encode($activities);

$conn->close();
?> 