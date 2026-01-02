<?php
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM vehicles WHERE id = $id");
    header('Location: manage_vehicles.php');
    exit();
}

// Get all vehicles with driver information
$query = "SELECT v.*, u.full_name as driver_name, u.phone as driver_phone 
          FROM vehicles v 
          LEFT JOIN users u ON v.current_driver_id = u.id 
          ORDER BY v.created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles - CRI Travels</title>
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
        .back-btn, .add-btn {
            background: #205887;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }
        .add-btn {
            background: #ffd600;
            color: #205887;
        }
        .button-group {
            display: flex;
            gap: 10px;
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
        .action-btn {
            padding: 6px 12px;
            margin: 0 5px;
            border-radius: 15px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .edit-btn {
            background: #ffd600;
            color: #205887;
        }
        .delete-btn {
            background: #f44336;
            color: #fff;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-active {
            background: #4caf50;
            color: #fff;
        }
        .status-maintenance {
            background: #ff9800;
            color: #fff;
        }
        .status-inactive {
            background: #9e9e9e;
            color: #fff;
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
            <h2 style="color: #205887;">Manage Vehicles</h2>
            <div class="button-group">
                <a href="add_vehicle.php" class="add-btn">Add New Vehicle</a>
                <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehicle Number</th>
                    <th>Type</th>
                    <th>Make/Model</th>
                    <th>Year</th>
                    <th>Capacity</th>
                    <th>Color</th>
                    <th>Current Driver</th>
                    <th>Insurance Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vehicle = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $vehicle['id']; ?></td>
                    <td><?php echo htmlspecialchars($vehicle['vehicle_number']); ?></td>
                    <td><?php echo ucfirst($vehicle['vehicle_type']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></td>
                    <td><?php echo $vehicle['year']; ?></td>
                    <td><?php echo $vehicle['capacity']; ?></td>
                    <td><?php echo htmlspecialchars($vehicle['color']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['driver_name'] ?? 'Unassigned'); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($vehicle['insurance_expiry'])); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $vehicle['status']; ?>">
                            <?php echo ucfirst($vehicle['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_vehicle.php?id=<?php echo $vehicle['id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="manage_vehicles.php?delete=<?php echo $vehicle['id']; ?>" 
                           class="action-btn delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this vehicle?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
