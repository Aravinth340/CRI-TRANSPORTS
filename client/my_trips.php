<?php
require_once '../config/auth.php';
requireRole('client');

$user = getCurrentUser();
$conn = getDBConnection();

// Get all client's trips
$clientId = $user['id'];
$query = "SELECT t.*, d.full_name as driver_name, d.phone as driver_phone 
          FROM trips t 
          LEFT JOIN users d ON t.driver_id = d.id 
          WHERE t.client_id = ? 
          ORDER BY t.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $clientId);
$stmt->execute();
$trips = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips - CRI Travels</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .back-btn {
            background: #205887;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
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
        tr:hover {
            background: #f5f5f5;
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
        <h1>CRI Travels - My Trips</h1>
    </header>
    
    <div class="container">
        <div class="header-section">
            <h2 style="color: #205887;">My Trips</h2>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
        
        <?php if ($trips->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Service Type</th>
                    <th>From → To</th>
                    <th>Date & Time</th>
                    <th>Passengers</th>
                    <th>Driver</th>
                    <th>Driver Phone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($trip = $trips->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $trip['id']; ?></td>
                    <td><?php echo ucfirst($trip['service_type']); ?></td>
                    <td style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 25)); ?> → 
                        <?php echo htmlspecialchars(substr($trip['destination'], 0, 25)); ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($trip['travel_date'])) . '<br>' . $trip['travel_time']; ?></td>
                    <td><?php echo $trip['passengers']; ?></td>
                    <td><?php echo htmlspecialchars($trip['driver_name'] ?? 'Not Assigned'); ?></td>
                    <td><?php echo htmlspecialchars($trip['driver_phone'] ?? 'N/A'); ?></td>
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
        <p style="text-align: center; color: #666; padding: 40px;">
            No trips booked yet. <a href="book_trip.php" style="color: #205887; font-weight: bold;">Book your first trip!</a>
        </p>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
