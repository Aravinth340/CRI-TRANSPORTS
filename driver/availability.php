<?php
require_once '../config/auth.php';
requireRole('driver');

$user = getCurrentUser();
$conn = getDBConnection();
$success = '';
$error = '';

// Get current driver info - Fixed: use get_result() before fetch_assoc()
$stmt = $conn->prepare("SELECT * FROM drivers WHERE user_id = ?");
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result(); // Get result object first
$driverInfo = $result->fetch_assoc(); // Then fetch from result
$stmt->close();

// If no driver record found, create default
if (!$driverInfo) {
    $driverInfo = ['availability' => 'offline'];
}

// Handle availability update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newAvailability = $_POST['availability'];
    
    $stmt = $conn->prepare("UPDATE drivers SET availability = ? WHERE user_id = ?");
    if ($stmt === false) {
        $error = "Error preparing update: " . $conn->error;
    } else {
        $stmt->bind_param("si", $newAvailability, $user['id']);
        
        if ($stmt->execute()) {
            $success = 'Availability updated successfully!';
            $driverInfo['availability'] = $newAvailability;
        } else {
            $error = "Error updating availability: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Availability - CRI Travels Driver</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header {
            background: #205887;
            color: white;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
        }
        
        header img {
            height: 50px;
            width: auto;
        }
        
        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 40px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 90%;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .header-section h2 {
            color: #205887;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .back-btn {
            background: #205887;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .back-btn:hover {
            background: #164a6b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
        }
        
        form {
            width: 100%;
        }
        
        .status-option {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            background: #fff;
        }
        
        .status-option:hover {
            border-color: #205887;
            background: #f8f9fa;
            transform: translateX(5px);
        }
        
        .status-option input[type="radio"] {
            margin-top: 3px;
            transform: scale(1.3);
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .status-option.selected {
            border-color: #205887;
            background: #e3f2fd;
            box-shadow: 0 2px 8px rgba(32, 88, 135, 0.2);
        }
        
        .status-content {
            flex: 1;
        }
        
        .status-content strong {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        
        .status-content span {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .status-available strong {
            color: #4caf50;
        }
        
        .status-busy strong {
            color: #ff9800;
        }
        
        .status-offline strong {
            color: #f44336;
        }
        
        .submit-btn {
            background: #ffd600;
            color: #205887;
            padding: 16px 30px;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(255, 214, 0, 0.3);
        }
        
        .submit-btn:hover {
            background: #fcb900;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 214, 0, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        footer {
            background: #205887;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
            width: 100%;
        }
        
        footer p {
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 25px;
                margin: 20px auto;
            }
            
            .header-section {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .header-section h2 {
                font-size: 1.5rem;
            }
            
            header {
                padding: 15px 20px;
            }
            
            header h1 {
                font-size: 1.2rem;
            }
            
            .status-option {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Update Availability</h1>
    </header>
    
    <div class="main-content">
        <div class="container">
            <div class="header-section">
                <h2>Update Status</h2>
                <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <label class="status-option <?php echo ($driverInfo['availability'] ?? 'offline') === 'available' ? 'selected' : ''; ?> status-available">
                    <input type="radio" name="availability" value="available" 
                        <?php echo ($driverInfo['availability'] ?? 'offline') === 'available' ? 'checked' : ''; ?> required>
                    <div class="status-content">
                        <strong>Available</strong>
                        <span>Ready to accept new trip assignments</span>
                    </div>
                </label>
                
                <label class="status-option <?php echo ($driverInfo['availability'] ?? 'offline') === 'busy' ? 'selected' : ''; ?> status-busy">
                    <input type="radio" name="availability" value="busy" 
                        <?php echo ($driverInfo['availability'] ?? 'offline') === 'busy' ? 'checked' : ''; ?> required>
                    <div class="status-content">
                        <strong>Busy</strong>
                        <span>Currently on a trip or temporarily unavailable</span>
                    </div>
                </label>
                
                <label class="status-option <?php echo ($driverInfo['availability'] ?? 'offline') === 'offline' ? 'selected' : ''; ?> status-offline">
                    <input type="radio" name="availability" value="offline" 
                        <?php echo ($driverInfo['availability'] ?? 'offline') === 'offline' ? 'checked' : ''; ?> required>
                    <div class="status-content">
                        <strong>Offline</strong>
                        <span>Not available for trips</span>
                    </div>
                </label>
                
                <button type="submit" class="submit-btn">Update Availability</button>
            </form>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
    
    <script>
        document.querySelectorAll('.status-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.status-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html>
