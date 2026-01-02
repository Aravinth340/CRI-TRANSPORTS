<?php
require_once '../config/auth.php';
requireRole('client');

$user = getCurrentUser();
$conn = getDBConnection();
$success = '';
$error = '';

// Handle trip booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceType = $_POST['service_type'];
    $pickupLocation = $_POST['pickup_location'];
    $destination = $_POST['destination'];
    $travelDate = $_POST['travel_date'];
    $travelTime = $_POST['travel_time'];
    $passengers = intval($_POST['passengers']);
    $specialRequests = $_POST['special_requests'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO trips (client_id, service_type, pickup_location, destination, travel_date, travel_time, passengers, special_requests, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    if ($stmt === false) {
        $error = 'Database error: ' . $conn->error;
    } else {
        $stmt->bind_param("isssssis", $user['id'], $serviceType, $pickupLocation, $destination, $travelDate, $travelTime, $passengers, $specialRequests);
        
        if ($stmt->execute()) {
            $success = 'Trip booked successfully! We will assign a driver shortly.';
        } else {
            $error = 'Error booking trip: ' . $stmt->error;
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
    <title>Book a Trip - CRI Travels</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #205887;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        .submit-btn {
            background: #ffd600;
            color: #205887;
            padding: 14px 30px;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
        }
        .submit-btn:hover {
            background: #fcb900;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <img src="../img/logo.png" alt="logo">
        <h1>CRI Travels - Book a Trip</h1>
    </header>
    
    <div class="container">
        <div class="header-section">
            <h2 style="color: #205887;">Book Your Trip</h2>
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="service_type">Service Type *</label>
                <select id="service_type" name="service_type" required>
                    <option value="">Select Service Type</option>
                    <option value="airport">Airport Transfer</option>
                    <option value="rental">Car Rental</option>
                    <option value="tour">City Tour</option>
                    <option value="business">Business Trip</option>
                    <option value="event">Event Transportation</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="pickup_location">Pickup Location *</label>
                <input type="text" id="pickup_location" name="pickup_location" placeholder="Enter pickup address" required>
            </div>
            
            <div class="form-group">
                <label for="destination">Destination *</label>
                <input type="text" id="destination" name="destination" placeholder="Enter destination address" required>
            </div>
            
            <div class="form-group">
                <label for="travel_date">Travel Date *</label>
                <input type="date" id="travel_date" name="travel_date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="travel_time">Travel Time *</label>
                <input type="time" id="travel_time" name="travel_time" required>
            </div>
            
            <div class="form-group">
                <label for="passengers">Number of Passengers *</label>
                <input type="number" id="passengers" name="passengers" min="1" max="50" required>
            </div>
            
            <div class="form-group">
                <label for="special_requests">Special Requests</label>
                <textarea id="special_requests" name="special_requests" rows="4" placeholder="Any special requirements or requests..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Book Trip</button>
        </form>
    </div>
    
    <footer>
        <p>&copy; 2025 CRI Travels. All rights reserved.</p>
    </footer>
</body>
</html>
