<?php
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id AND user_type = 'client'");
    header('Location: manage_clients.php');
    exit();
}

// Get all clients
$query = "SELECT u.*, c.address, c.city, c.state, c.pincode, c.emergency_contact 
          FROM users u 
          LEFT JOIN clients c ON u.id = c.user_id 
          WHERE u.user_type = 'client' 
          ORDER BY u.created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients - CRI Travels</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .container {
            max-width: 1200px;
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
            <h2 style="color: #205887;">Manage Clients</h2>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($client = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $client['id']; ?></td>
                    <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                    <td><?php echo htmlspecialchars($client['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($client['city'] ?? 'N/A'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $client['status']; ?>">
                            <?php echo ucfirst($client['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($client['created_at'])); ?></td>
                    <td>
                        <a href="edit_client.php?id=<?php echo $client['id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="manage_clients.php?delete=<?php echo $client['id']; ?>" 
                           class="action-btn delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this client?')">Delete</a>
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
