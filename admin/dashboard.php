<?php
require_once '../config/auth.php';
requireRole('admin');

$user = getCurrentUser();
$conn = getDBConnection();

// Get statistics
$clientCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'client'")->fetch_assoc()['count'];
$driverCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'driver'")->fetch_assoc()['count'];
$tripCount = $conn->query("SELECT COUNT(*) as count FROM trips")->fetch_assoc()['count'];
$pendingTrips = $conn->query("SELECT COUNT(*) as count FROM trips WHERE status = 'pending'")->fetch_assoc()['count'];
$vehicleCount = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'];
$activeVehicles = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'active'")->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CRI Travels</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .welcome-section {
            background: #205887;
            color: #fff;
            padding: 30px;
            border-radius: 14px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #205887;
            font-size: 2.5rem;
            margin: 10px 0;
        }
        .stat-card p {
            color: #666;
            font-size: 1.1rem;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-card h3 {
            color: #205887;
            margin-bottom: 15px;
        }
        .menu-card a {
            display: inline-block;
            background: #ffd600;
            color: #205887;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            margin-top: 15px;
        }
        .menu-card a:hover {
            background: #fcb900;
        }
        .logout-btn {
            background: #ef8f2d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Admin Panel</h1>
    </header>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p>Admin Dashboard - Manage your travel business</p>
            <a href="config\logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p>Total Clients</p>
                <h3><?php echo $clientCount; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Drivers</p>
                <h3><?php echo $driverCount; ?></h3>
            </div>
            <!-- Added vehicle statistics cards -->
            <div class="stat-card">
                <p>Total Vehicles</p>
                <h3><?php echo $vehicleCount; ?></h3>
            </div>
            <div class="stat-card">
                <p>Active Vehicles</p>
                <h3><?php echo $activeVehicles; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Trips</p>
                <h3><?php echo $tripCount; ?></h3>
            </div>
            <div class="stat-card">
                <p>Pending Trips</p>
                <h3><?php echo $pendingTrips; ?></h3>
            </div>
        </div>
        
        <div class="menu-grid">
            <div class="menu-card">
                <h3>Manage Clients</h3>
                <p>View, edit, and manage all client accounts and details</p>
                <a href="manage_clients.php">Manage Clients</a>
            </div>
            
            <div class="menu-card">
                <h3>Manage Drivers</h3>
                <p>View, edit, and manage driver profiles and availability</p>
                <a href="manage_drivers.php">Manage Drivers</a>
            </div>
            
            <!-- Added vehicle management menu card -->
            <div class="menu-card">
                <h3>Manage Vehicles</h3>
                <p>View, add, edit, and track all vehicles in the fleet</p>
                <a href="manage_vehicles.php">Manage Vehicles</a>
            </div>
            
            <div class="menu-card">
                <h3>Manage Trips</h3>
                <p>View, assign, and track all trip bookings</p>
                <a href="manage_trips.php">Manage Trips</a>
            </div>
            
            <div class="menu-card">
                <h3>Edit Website Content</h3>
                <p>Edit banner text, slider images, events, services, and page content</p>
                <a href="editor.php">Edit Content</a>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
