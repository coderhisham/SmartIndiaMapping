<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in and return JSON response
echo json_encode(['loggedIn' => isLoggedIn()]);
?> 