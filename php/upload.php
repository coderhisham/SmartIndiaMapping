<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get parameters
$resourceType = isset($_POST['resource_type']) ? sanitizeInput($_POST['resource_type']) : '';
$replaceData = isset($_POST['replace_data']) && $_POST['replace_data'] === 'on';

// Validate resource type
if (!isValidResourceType($resourceType)) {
    echo json_encode(['success' => false, 'message' => 'Invalid resource type']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

// Check file type (must be CSV)
$fileInfo = pathinfo($_FILES['csv_file']['name']);
if (strtolower($fileInfo['extension']) !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'File must be a CSV']);
    exit();
}

// Get database connection
$conn = getDBConnection();

// Get table name
$tableName = getResourceTableName($resourceType);

// If replacing data, truncate the table first
if ($replaceData) {
    $truncateQuery = "TRUNCATE TABLE $tableName";
    if (!$conn->query($truncateQuery)) {
        echo json_encode(['success' => false, 'message' => 'Failed to clear existing data: ' . $conn->error]);
        $conn->close();
        exit();
    }
    
    logActivity("Cleared all data for $resourceType");
}

// Open the uploaded file
$file = fopen($_FILES['csv_file']['tmp_name'], 'r');
if (!$file) {
    echo json_encode(['success' => false, 'message' => 'Failed to open uploaded file']);
    $conn->close();
    exit();
}

// Get the header row
$headers = fgetcsv($file);
if (!$headers) {
    echo json_encode(['success' => false, 'message' => 'CSV file is empty or not formatted correctly']);
    fclose($file);
    $conn->close();
    exit();
}

// Process each resource type differently
$insertCount = 0;
$errorCount = 0;

// Normalize headers (convert to lowercase, remove spaces)
$normalizedHeaders = array_map(function($header) {
    return strtolower(str_replace(' ', '_', trim($header)));
}, $headers);

// Begin transaction
$conn->begin_transaction();

try {
    // Process each row in the CSV
    while (($row = fgetcsv($file)) !== false) {
        // Skip empty rows
        if (count($row) <= 1 && empty($row[0])) {
            continue;
        }
        
        // Create an associative array with header => value
        $data = array_combine($normalizedHeaders, $row);
        
        // Build INSERT query based on resource type
        switch ($resourceType) {
            case 'schools':
                $query = insertSchool($tableName, $data);
                break;
            case 'hospitals':
                $query = insertHospital($tableName, $data);
                break;
            case 'water':
                $query = insertWater($tableName, $data);
                break;
            case 'electricity':
                $query = insertElectricity($tableName, $data);
                break;
            default:
                throw new Exception('Invalid resource type');
        }
        
        // Execute the query
        if ($conn->query($query)) {
            $insertCount++;
        } else {
            $errorCount++;
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Log the activity
    logActivity("Uploaded $insertCount $resourceType resources ($errorCount errors)");
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully imported $insertCount records with $errorCount errors",
        'inserted' => $insertCount,
        'errors' => $errorCount
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error processing CSV: ' . $e->getMessage()
    ]);
}

fclose($file);
$conn->close();

// Helper functions for each resource type
function insertSchool($tableName, $data) {
    global $conn;
    
    // Required fields
    $name = $conn->real_escape_string($data['name'] ?? '');
    $state = $conn->real_escape_string($data['state'] ?? '');
    $latitude = floatval($data['latitude'] ?? 0);
    $longitude = floatval($data['longitude'] ?? 0);
    $level = $conn->real_escape_string($data['level'] ?? 'primary');
    
    // Optional fields
    $district = isset($data['district']) ? "'" . $conn->real_escape_string($data['district']) . "'" : 'NULL';
    $students = isset($data['students']) ? intval($data['students']) : 0;
    $teachers = isset($data['teachers']) ? intval($data['teachers']) : 0;
    $established_year = isset($data['established_year']) ? intval($data['established_year']) : 'NULL';
    $facilities = isset($data['facilities']) ? "'" . $conn->real_escape_string($data['facilities']) . "'" : 'NULL';
    
    // Validate level
    $validLevels = ['primary', 'secondary', 'higher', 'university'];
    if (!in_array($level, $validLevels)) {
        $level = 'primary';
    }
    
    return "INSERT INTO $tableName (name, state, district, latitude, longitude, level, students, teachers, established_year, facilities) 
            VALUES ('$name', '$state', $district, $latitude, $longitude, '$level', $students, $teachers, $established_year, $facilities)";
}

function insertHospital($tableName, $data) {
    global $conn;
    
    // Required fields
    $name = $conn->real_escape_string($data['name'] ?? '');
    $state = $conn->real_escape_string($data['state'] ?? '');
    $latitude = floatval($data['latitude'] ?? 0);
    $longitude = floatval($data['longitude'] ?? 0);
    $hospital_type = $conn->real_escape_string($data['hospital_type'] ?? 'government');
    
    // Optional fields
    $district = isset($data['district']) ? "'" . $conn->real_escape_string($data['district']) . "'" : 'NULL';
    $beds = isset($data['beds']) ? intval($data['beds']) : 0;
    $doctors = isset($data['doctors']) ? intval($data['doctors']) : 0;
    $specialties = isset($data['specialties']) ? "'" . $conn->real_escape_string($data['specialties']) . "'" : 'NULL';
    $emergency_services = isset($data['emergency_services']) && ($data['emergency_services'] === 'yes' || $data['emergency_services'] === '1') ? 1 : 0;
    
    // Validate hospital type
    $validTypes = ['government', 'private', 'charity'];
    if (!in_array($hospital_type, $validTypes)) {
        $hospital_type = 'government';
    }
    
    return "INSERT INTO $tableName (name, state, district, latitude, longitude, hospital_type, beds, doctors, specialties, emergency_services) 
            VALUES ('$name', '$state', $district, $latitude, $longitude, '$hospital_type', $beds, $doctors, $specialties, $emergency_services)";
}

function insertWater($tableName, $data) {
    global $conn;
    
    // Required fields
    $name = $conn->real_escape_string($data['name'] ?? '');
    $state = $conn->real_escape_string($data['state'] ?? '');
    $latitude = floatval($data['latitude'] ?? 0);
    $longitude = floatval($data['longitude'] ?? 0);
    $source_type = $conn->real_escape_string($data['source_type'] ?? 'dam');
    
    // Optional fields
    $district = isset($data['district']) ? "'" . $conn->real_escape_string($data['district']) . "'" : 'NULL';
    $capacity = isset($data['capacity']) ? floatval($data['capacity']) : 0;
    $quality_index = isset($data['quality_index']) ? floatval($data['quality_index']) : 'NULL';
    $serves_population = isset($data['serves_population']) ? intval($data['serves_population']) : 0;
    $year_established = isset($data['year_established']) ? intval($data['year_established']) : 'NULL';
    
    // Validate source type
    $validTypes = ['dam', 'lake', 'river', 'well', 'treatment_plant'];
    if (!in_array($source_type, $validTypes)) {
        $source_type = 'dam';
    }
    
    return "INSERT INTO $tableName (name, state, district, latitude, longitude, source_type, capacity, quality_index, serves_population, year_established) 
            VALUES ('$name', '$state', $district, $latitude, $longitude, '$source_type', $capacity, $quality_index, $serves_population, $year_established)";
}

function insertElectricity($tableName, $data) {
    global $conn;
    
    // Required fields
    $name = $conn->real_escape_string($data['name'] ?? '');
    $state = $conn->real_escape_string($data['state'] ?? '');
    $latitude = floatval($data['latitude'] ?? 0);
    $longitude = floatval($data['longitude'] ?? 0);
    $power_type = $conn->real_escape_string($data['power_type'] ?? 'hydro');
    
    // Optional fields
    $district = isset($data['district']) ? "'" . $conn->real_escape_string($data['district']) . "'" : 'NULL';
    $capacity = isset($data['capacity']) ? floatval($data['capacity']) : 0;
    $serves_areas = isset($data['serves_areas']) ? "'" . $conn->real_escape_string($data['serves_areas']) . "'" : 'NULL';
    $year_established = isset($data['year_established']) ? intval($data['year_established']) : 'NULL';
    
    // Validate power type
    $validTypes = ['hydro', 'thermal', 'nuclear', 'solar', 'wind', 'substation'];
    if (!in_array($power_type, $validTypes)) {
        $power_type = 'hydro';
    }
    
    return "INSERT INTO $tableName (name, state, district, latitude, longitude, power_type, capacity, serves_areas, year_established) 
            VALUES ('$name', '$state', $district, $latitude, $longitude, '$power_type', $capacity, $serves_areas, $year_established)";
}
?> 