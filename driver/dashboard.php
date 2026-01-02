<?php
require_once '../config/auth.php';
requireRole('driver');

$user = getCurrentUser();
$conn = getDBConnection();

// Get driver information with vehicle details - Fixed: join with vehicles table
$stmt = $conn->prepare("SELECT d.*, v.vehicle_type, v.vehicle_number 
                        FROM drivers d 
                        LEFT JOIN vehicles v ON d.vehicle_id = v.id 
                        WHERE d.user_id = ?");
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$driverInfo = $result->fetch_assoc();
$stmt->close();

// If no driver record found, create default
if (!$driverInfo) {
    $driverInfo = [
        'availability' => 'offline',
        'license_number' => 'N/A',
        'vehicle_type' => null,
        'vehicle_number' => null
    ];
}

// Get driver's trips statistics
$driverId = $user['id'];
$totalTrips = $conn->query("SELECT COUNT(*) as count FROM trips WHERE driver_id = {$driverId}")->fetch_assoc()['count'];
$activeTrips = $conn->query("SELECT COUNT(*) as count FROM trips WHERE driver_id = {$driverId} AND status IN ('confirmed', 'in_progress')")->fetch_assoc()['count'];
$completedTrips = $conn->query("SELECT COUNT(*) as count FROM trips WHERE driver_id = {$driverId} AND status = 'completed'")->fetch_assoc()['count'];

// Get assigned trips
$assignedTripsQuery = "SELECT t.*, c.full_name as client_name, c.phone as client_phone 
                       FROM trips t 
                       JOIN users c ON t.client_id = c.id 
                       WHERE t.driver_id = {$driverId} AND t.status IN ('confirmed', 'in_progress')
                       ORDER BY t.travel_date ASC, t.travel_time ASC";
$assignedTrips = $conn->query($assignedTripsQuery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - CRI Travels</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout-btn {
            background: #ef8f2d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
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
        .availability-card {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
            margin-bottom: 30px;
        }
        .availability-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .available { background: #4caf50; color: #fff; }
        .busy { background: #ff9800; color: #fff; }
        .offline { background: #f44336; color: #fff; }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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
        .trips-section {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #205887;
            color: #fff;
            font-weight: bold;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-confirmed { background: #2196f3; color: #fff; }
        .status-in_progress { background: #9c27b0; color: #fff; }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Driver Portal</h1>
    </header>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
                <p>Driver Dashboard - Manage your trips and availability</p>
            </div>
            <a href="../config/logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="availability-card">
            <h3 style="color: #205887; margin-bottom: 15px;">Current Status</h3>
            <span class="availability-badge <?php echo htmlspecialchars($driverInfo['availability'] ?? 'offline'); ?>">
                <?php echo ucfirst($driverInfo['availability'] ?? 'offline'); ?>
            </span>
            <p style="margin-top: 15px; color: #666;">
                Vehicle: <?php echo $driverInfo['vehicle_type'] ? ucfirst($driverInfo['vehicle_type']) : 'Not Assigned'; ?>
                <?php if ($driverInfo['vehicle_number']): ?>
                    (<?php echo htmlspecialchars($driverInfo['vehicle_number']); ?>)
                <?php endif; ?>
                | 
                License: <?php echo htmlspecialchars($driverInfo['license_number'] ?? 'N/A'); ?>
            </p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p>Total Trips</p>
                <h3><?php echo $totalTrips; ?></h3>
            </div>
            <div class="stat-card">
                <p>Active Trips</p>
                <h3><?php echo $activeTrips; ?></h3>
            </div>
            <div class="stat-card">
                <p>Completed Trips</p>
                <h3><?php echo $completedTrips; ?></h3>
            </div>
        </div>
        
        <div class="menu-grid">
            <div class="menu-card">
                <h3>My Trips</h3>
                <p>View all assigned trips and trip history</p>
                <a href="my_trips.php">View All Trips</a>
            </div>
            
            <div class="menu-card">
                <h3>Update Availability</h3>
                <p>Change your availability status</p>
                <a href="availability.php">Update Status</a>
            </div>
            
            <div class="menu-card">
                <h3>My Profile</h3>
                <p>Update your driver information</p>
                <a href="profile.php">Edit Profile</a>
            </div>
        </div>
        
        <div class="trips-section">
            <h2 style="color: #205887; margin-bottom: 20px;">Active Trips</h2>
            <?php if ($assignedTrips && $assignedTrips->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Trip ID</th>
                        <th>Client</th>
                        <th>Client Phone</th>
                        <th>Service Type</th>
                        <th>From → To</th>
                        <th>Date & Time</th>
                        <th>Passengers</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trip = $assignedTrips->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $trip['id']; ?></td>
                        <td><?php echo htmlspecialchars($trip['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($trip['client_phone'] ?? 'N/A'); ?></td>
                        <td><?php echo ucfirst($trip['service_type']); ?></td>
                        <td style="font-size: 0.9rem;">
                            <?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 25)); ?> → 
                            <?php echo htmlspecialchars(substr($trip['destination'], 0, 25)); ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($trip['travel_date'])) . '<br>' . htmlspecialchars($trip['travel_time']); ?></td>
                        <td><?php echo $trip['passengers']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($trip['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $trip['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: #666; padding: 20px;">No active trips assigned at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
