<?php
require_once '../config/auth.php';
requireRole('client');

$user = getCurrentUser();
$conn = getDBConnection();

// Get client information
$stmt = $conn->prepare("SELECT * FROM clients WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$clientInfo = $result->fetch_assoc();
$stmt->close();

// Get client's trips statistics
$clientId = $user['id'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM trips WHERE client_id = ?");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$totalTrips = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM trips WHERE client_id = ? AND status = 'pending'");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$pendingTrips = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM trips WHERE client_id = ? AND status = 'completed'");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$completedTrips = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get recent trips
$recentTripsQuery = "SELECT t.*, d.full_name as driver_name 
                     FROM trips t 
                     LEFT JOIN users d ON t.driver_id = d.id 
                     WHERE t.client_id = ? 
                     ORDER BY t.created_at DESC LIMIT 5";
$stmt = $conn->prepare($recentTripsQuery);
$stmt->bind_param("i", $clientId);
$stmt->execute();
$recentTrips = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - CRI Travels</title>
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
        .status-pending { background: #ff9800; color: #fff; }
        .status-confirmed { background: #2196f3; color: #fff; }
        .status-in_progress { background: #9c27b0; color: #fff; }
        .status-completed { background: #4caf50; color: #fff; }
        .status-cancelled { background: #f44336; color: #fff; }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Client Portal</h1>
    </header>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
                <p>Client Dashboard - Book and manage your trips</p>
            </div>
            <a href="../config/logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p>Total Trips</p>
                <h3><?php echo $totalTrips; ?></h3>
            </div>
            <div class="stat-card">
                <p>Pending Trips</p>
                <h3><?php echo $pendingTrips; ?></h3>
            </div>
            <div class="stat-card">
                <p>Completed Trips</p>
                <h3><?php echo $completedTrips; ?></h3>
            </div>
        </div>
        
        <div class="menu-grid">
            <div class="menu-card">
                <h3>Book a Trip</h3>
                <p>Schedule a new trip with CRI Travels</p>
                <a href="book_trip.php">Book Now</a>
            </div>
            
            <div class="menu-card">
                <h3>My Trips</h3>
                <p>View and manage all your trip bookings</p>
                <a href="my_trips.php">View Trips</a>
            </div>
            
            <div class="menu-card">
                <h3>My Profile</h3>
                <p>Update your personal information</p>
                <a href="profile.php">Edit Profile</a>
            </div>
        </div>
        
        <div class="trips-section">
            <h2 style="color: #205887; margin-bottom: 20px;">Recent Trips</h2>
            <?php if ($recentTrips->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Service Type</th>
                        <th>From → To</th>
                        <th>Date & Time</th>
                        <th>Driver</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trip = $recentTrips->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo ucfirst($trip['service_type']); ?></td>
                        <td><?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 30)) . ' → ' . htmlspecialchars(substr($trip['destination'], 0, 30)); ?></td>
                        <td><?php echo date('M d, Y', strtotime($trip['travel_date'])) . ' ' . $trip['travel_time']; ?></td>
                        <td><?php echo htmlspecialchars($trip['driver_name'] ?? 'Not Assigned'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $trip['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $trip['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: #666; padding: 20px;">No trips yet. Book your first trip to get started!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
