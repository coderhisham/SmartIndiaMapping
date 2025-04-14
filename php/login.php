<?php
require_once 'config.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from form
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        // Redirect back to login page with error
        header("Location: ../admin.html?error=login");
        exit();
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, username, password FROM " . USER_TABLE . " WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            session_regenerate_id();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Update last login time
            $updateStmt = $conn->prepare("UPDATE " . USER_TABLE . " SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Log activity
            logActivity("User logged in", $user['id']);
            
            // Redirect to admin panel
            header("Location: ../admin-panel.html");
            exit();
        } else {
            // Password is incorrect
            header("Location: ../admin.html?error=login");
            exit();
        }
    } else {
        // Username not found
        header("Location: ../admin.html?error=login");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    // Not a POST request, redirect to login page
    header("Location: ../admin.html");
    exit();
}
?> 