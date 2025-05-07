<?php
// Include session management functions
require_once 'php/session.php';

// Start a secure session
session_start_secure();

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart India Mapping - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-dark: #1a1a2e;
            --background-medium: #16213e;
            --background-light: #0f3460;
            --text-color: #f0f0f0;
            --border-color: #2c3e50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background-dark);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/india-map-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            background-color: var(--background-medium);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            width: 90%;
            max-width: 400px;
            border: 1px solid var(--border-color);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #ccc;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background-color: var(--background-light);
            color: var(--text-color);
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.25);
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid #e74c3c;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            display: none;
        }
        
        .back-to-map {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-to-map a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .back-to-map a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Smart India Mapping</h1>
            <p>Admin Dashboard Login</p>
        </div>
        
        <div class="error-message" id="error-message">
            Invalid username or password. Please try again.
        </div>
        
        <form action="php/login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-to-map">
            <a href="map.html"><i class="fas fa-map-marked-alt"></i> Back to Map</a>
        </div>
    </div>
    
    <script>
        // Check for error parameter in URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            
            if (error) {
                const errorElement = document.getElementById('error-message');
                errorElement.textContent = error;
                errorElement.style.display = 'block';
            }
        };
    </script>
</body>
</html> 