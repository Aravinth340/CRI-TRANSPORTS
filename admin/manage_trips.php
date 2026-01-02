<?php
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $tripId = intval($_POST['trip_id']);
    $status = $_POST['status'];
    $driverId = !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : null;
    
    if ($driverId) {
        $stmt = $conn->prepare("UPDATE trips SET status = ?, driver_id = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sii", $status, $driverId, $tripId);
            if ($stmt->execute()) {
                $success_message = "Trip updated successfully!";
            } else {
                $error_message = "Error updating trip: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    } else {
        $stmt = $conn->prepare("UPDATE trips SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $tripId);
            if ($stmt->execute()) {
                $success_message = "Trip status updated successfully!";
            } else {
                $error_message = "Error updating trip: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
    
    // Only redirect if there's no error, otherwise show the error
    if (!isset($error_message)) {
        header('Location: manage_trips.php');
        exit();
    }
}

// Get all trips
$query = "SELECT t.*, 
          c.full_name as client_name, c.email as client_email,
          d.full_name as driver_name
          FROM trips t
          JOIN users c ON t.client_id = c.id
          LEFT JOIN users d ON t.driver_id = d.id
          ORDER BY t.created_at DESC";
$result = $conn->query($query);

// Check if query was successful
if ($result === false) {
    $error_message = "Error loading trips: " . $conn->error;
    $result = null;
}

// Get available drivers - Fixed query to join with vehicles table
$driversQuery = "SELECT u.id, u.full_name, v.vehicle_type, dr.availability 
                 FROM users u 
                 JOIN drivers dr ON u.id = dr.user_id 
                 LEFT JOIN vehicles v ON dr.vehicle_id = v.id
                 WHERE u.user_type = 'driver' AND dr.availability IN ('available', 'busy')";
$driversResult = $conn->query($driversQuery);
$drivers = [];

// Check if drivers query was successful before fetching
if ($driversResult !== false) {
    while ($driver = $driversResult->fetch_assoc()) {
        $drivers[] = $driver;
    }
} else {
    // Log error but don't break the page
    $driver_error = "Error loading drivers: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trips - CRI Travels</title>
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
        }
        
        header {
            background: #205887;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        header img {
            height: 50px;
        }
        
        header h1 {
            font-size: 1.5rem;
        }
        
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
        
        .header-section h2 {
            color: #205887;
            font-size: 2rem;
        }
        
        .back-btn {
            background: #205887;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #164a6b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: linear-gradient(135deg, #205887 0%, #2a6fa5 100%);
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-pending { background: #ff9800; color: #fff; }
        .status-confirmed { background: #2196f3; color: #fff; }
        .status-in_progress { background: #9c27b0; color: #fff; }
        .status-completed { background: #4caf50; color: #fff; }
        .status-cancelled { background: #f44336; color: #fff; }
        
        .action-form {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .action-form select {
            padding: 6px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .action-form select:focus {
            outline: none;
            border-color: #205887;
        }
        
        .update-btn {
            padding: 6px 15px;
            background: #ffd600;
            color: #205887;
            border: none;
            border-radius: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
        }
        
        .update-btn:hover {
            background: #ffed4e;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        footer {
            background: #205887;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: #fff;
            margin: 50px auto;
            padding: 30px;
            border-radius: 14px;
            max-width: 600px;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Admin Panel</h1>
    </header>
    
    <div class="container">
        <div class="header-section">
            <h2>Manage Trips</h2>
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($driver_error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($driver_error); ?></div>
        <?php endif; ?>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Driver</th>
                        <th>Service</th>
                        <th>From → To</th>
                        <th>Date & Time</th>
                        <th>Passengers</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trip = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trip['id']); ?></td>
                        <td><?php echo htmlspecialchars($trip['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($trip['driver_name'] ?? 'Unassigned'); ?></td>
                        <td><?php echo ucfirst($trip['service_type']); ?></td>
                        <td style="font-size: 0.85rem;">
                            <?php echo htmlspecialchars(substr($trip['pickup_location'], 0, 20)); ?> → 
                            <?php echo htmlspecialchars(substr($trip['destination'], 0, 20)); ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($trip['travel_date'])); ?><br><?php echo htmlspecialchars($trip['travel_time']); ?></td>
                        <td><?php echo htmlspecialchars($trip['passengers']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($trip['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $trip['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="trip_id" value="<?php echo htmlspecialchars($trip['id']); ?>">
                                <select name="status" required>
                                    <option value="pending" <?php echo $trip['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $trip['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="in_progress" <?php echo $trip['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $trip['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $trip['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <select name="driver_id">
                                    <option value="">Assign Driver</option>
                                    <?php foreach ($drivers as $driver): ?>
                                        <option value="<?php echo htmlspecialchars($driver['id']); ?>"
                                            <?php echo $trip['driver_id'] == $driver['id'] ? 'selected' : ''; ?> <?php echo $driver['availability'] == 'busy' ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($driver['full_name']) . ' (' . ucfirst($driver['vehicle_type']) . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($result && $result->num_rows === 0): ?>
            <div class="no-data">No trips found.</div>
        <?php else: ?>
            <div class="error-message">Unable to load trips. Please check your database connection.</div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
