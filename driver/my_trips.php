<?php
require_once '../config/auth.php';
requireRole('driver');

$user = getCurrentUser();
$conn = getDBConnection();

// Handle trip status update
if (isset($_POST['update_status'])) {
    $tripId = intval($_POST['trip_id']);
    $newStatus = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE trips SET status = ? WHERE id = ? AND driver_id = ?");
    $stmt->bind_param("sii", $newStatus, $tripId, $user['id']);
    $stmt->execute();
    $stmt->close();
    
    header('Location: my_trips.php');
    exit();
}

// Get all driver's trips
$driverId = $user['id'];
$query = "SELECT t.*, c.full_name as client_name, c.phone as client_phone, c.email as client_email 
          FROM trips t 
          JOIN users c ON t.client_id = c.id 
          WHERE t.driver_id = {$driverId} 
          ORDER BY t.travel_date DESC, t.travel_time DESC";
$trips = $conn->query($query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips - CRI Travels Driver</title>
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
            font-size: 0.9rem;
        }
        th, td {
            padding: 10px;
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
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-confirmed { background: #2196f3; color: #fff; }
        .status-in_progress { background: #9c27b0; color: #fff; }
        .status-completed { background: #4caf50; color: #fff; }
        .status-cancelled { background: #f44336; color: #fff; }
        .action-form {
            display: inline-block;
        }
        .action-form select {
            padding: 5px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            margin-right: 5px;
        }
        .update-btn {
            padding: 6px 12px;
            background: #ffd600;
            color: #205887;
            border: none;
            border-radius: 15px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Driver Portal</h1>
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
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Service Type</th>
                    <th>From → To</th>
                    <th>Date & Time</th>
                    <th>Passengers</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($trip = $trips->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $trip['id']; ?></td>
                    <td><?php echo htmlspecialchars($trip['client_name']); ?></td>
                    <td style="font-size: 0.85rem;">
                        <?php echo htmlspecialchars($trip['client_phone']); ?><br>
                        <?php echo htmlspecialchars($trip['client_email']); ?>
                    </td>
                    <td><?php echo ucfirst($trip['service_type']); ?></td>
                    <td style="font-size: 0.85rem;">
                        <?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 20)); ?> → 
                        <?php echo htmlspecialchars(substr($trip['destination'], 0, 20)); ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($trip['travel_date'])) . '<br>' . $trip['travel_time']; ?></td>
                    <td><?php echo $trip['passengers']; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $trip['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $trip['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($trip['status'] !== 'completed' && $trip['status'] !== 'cancelled'): ?>
                        <form method="POST" class="action-form">
                            <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                            <select name="status" required>
                                <option value="confirmed" <?php echo $trip['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="in_progress" <?php echo $trip['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $trip['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status" class="update-btn">Update</button>
                        </form>
                        <?php else: ?>
                        <span style="color: #999;">No actions</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align: center; color: #666; padding: 40px;">No trips assigned yet.</p>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
