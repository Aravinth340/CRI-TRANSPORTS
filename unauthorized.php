<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized - CRI Travels</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #f44336;
            margin-bottom: 20px;
        }
        h1 {
            color: #205887;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            background: #ffd600;
            color: #205887;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>
    <header>
        <img src="img/logo.png" alt="logo">
        <h1>CRI Travels</h1>
    </header>
    
    <div class="error-container">
        <div class="error-icon">⛔</div>
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page.</p>
        <a href="login.php" class="btn">Go to Login</a>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
