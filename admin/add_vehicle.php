<?php
require_once '../config/auth.php';
requireRole('admin');

$conn = getDBConnection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_number = $_POST['vehicle_number'];
    $vehicle_type = $_POST['vehicle_type'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = intval($_POST['year']);
    $capacity = intval($_POST['capacity']);
    $color = $_POST['color'];
    $registration_date = $_POST['registration_date'];
    $insurance_expiry = $_POST['insurance_expiry'];
    $status = $_POST['status'];
    $current_driver_id = !empty($_POST['current_driver_id']) ? intval($_POST['current_driver_id']) : null;
    
    $stmt = $conn->prepare("INSERT INTO vehicles (vehicle_number, vehicle_type, make, model, year, capacity, color, registration_date, insurance_expiry, status, current_driver_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssissssi", $vehicle_number, $vehicle_type, $make, $model, $year, $capacity, $color, $registration_date, $insurance_expiry, $status, $current_driver_id);
    
    if ($stmt->execute()) {
        $message = '<div style="padding: 15px; background: #4caf50; color: #fff; border-radius: 8px; margin-bottom: 20px;">Vehicle added successfully!</div>';
    } else {
        $message = '<div style="padding: 15px; background: #f44336; color: #fff; border-radius: 8px; margin-bottom: 20px;">Error: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// Get available drivers
$drivers = $conn->query("SELECT id, full_name FROM users WHERE user_type = 'driver' ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle - CRI Travels</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(32,88,135,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #205887;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        input:focus, select:focus {
            border-color: #205887;
            outline: none;
        }
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .submit-btn, .back-btn {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        .submit-btn {
            background: #ffd600;
            color: #205887;
        }
        .back-btn {
            background: #205887;
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
        <h2 style="color: #205887; margin-bottom: 30px;">Add New Vehicle</h2>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="vehicle_number">Vehicle Number *</label>
                <input type="text" id="vehicle_number" name="vehicle_number" required>
            </div>
            
            <div class="form-group">
                <label for="vehicle_type">Vehicle Type *</label>
                <select id="vehicle_type" name="vehicle_type" required>
                    <option value="">Select Type</option>
                    <option value="auto">Auto</option>
                    <option value="car">Car</option>
                    <option value="maxicab">Maxi Cab</option>
                    <option value="coach">Coach</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="make">Make</label>
                <input type="text" id="make" name="make">
            </div>
            
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model">
            </div>
            
            <div class="form-group">
                <label for="year">Year *</label>
                <input type="number" id="year" name="year" min="1990" max="2025" required>
            </div>
            
            <div class="form-group">
                <label for="capacity">Capacity (Passengers) *</label>
                <input type="number" id="capacity" name="capacity" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color">
            </div>
            
            <div class="form-group">
                <label for="registration_date">Registration Date</label>
                <input type="date" id="registration_date" name="registration_date">
            </div>
            
            <div class="form-group">
                <label for="insurance_expiry">Insurance Expiry Date</label>
                <input type="date" id="insurance_expiry" name="insurance_expiry">
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="current_driver_id">Assign Driver (Optional)</label>
                <select id="current_driver_id" name="current_driver_id">
                    <option value="">Unassigned</option>
                    <?php while ($driver = $drivers->fetch_assoc()): ?>
                        <option value="<?php echo $driver['id']; ?>">
                            <?php echo htmlspecialchars($driver['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="button-group">
                <a href="manage_vehicles.php" class="back-btn">Cancel</a>
                <button type="submit" class="submit-btn">Add Vehicle</button>
            </div>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
