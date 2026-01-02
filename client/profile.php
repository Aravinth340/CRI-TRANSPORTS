<?php
require_once '../config/auth.php';

requireLogin();
requireRole('client');

$user = getCurrentUser();
$conn = getDBConnection();
$user_id = $user['id'];
$success = '';
$error = '';

$check_columns = "SHOW COLUMNS FROM clients";
$result = $conn->query($check_columns);
$client_columns = [];
while ($row = $result->fetch_assoc()) {
    $client_columns[] = $row['Field'];
}

$name_column = in_array('company_name', $client_columns) ? 'company_name' : 
               (in_array('name', $client_columns) ? 'name' : 
               (in_array('client_name', $client_columns) ? 'client_name' : 
               (in_array('business_name', $client_columns) ? 'business_name' : null)));

$phone_column = in_array('phone', $client_columns) ? 'phone' : 
                (in_array('contact', $client_columns) ? 'contact' : 
                (in_array('phone_number', $client_columns) ? 'phone_number' : null));

$address_column = in_array('address', $client_columns) ? 'address' : 
                  (in_array('location', $client_columns) ? 'location' : null);

$select_parts = ['u.*'];
if ($name_column) $select_parts[] = "c.$name_column as company_name";
if ($phone_column) $select_parts[] = "c.$phone_column as phone";
if ($address_column) $select_parts[] = "c.$address_column as address";

$query = "SELECT " . implode(', ', $select_parts) . " 
          FROM users u 
          LEFT JOIN clients c ON u.id = c.user_id 
          WHERE u.id = ?";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['name'];
    $email = $_POST['email'];
    $company_name = $_POST['company_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Update users table
    $update_user = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($update_user);
    
    if ($stmt === false) {
        $error = "Database error: " . $conn->error;
    } elseif ($stmt->bind_param("ssi", $full_name, $email, $user_id) && $stmt->execute()) {
        $update_parts = [];
        $param_types = '';
        $params = [];
        
        if ($name_column) {
            $update_parts[] = "$name_column = ?";
            $param_types .= 's';
            $params[] = &$company_name;
        }
        if ($phone_column) {
            $update_parts[] = "$phone_column = ?";
            $param_types .= 's';
            $params[] = &$phone;
        }
        if ($address_column) {
            $update_parts[] = "$address_column = ?";
            $param_types .= 's';
            $params[] = &$address;
        }
        
        if (!empty($update_parts)) {
            $param_types .= 'i';
            $params[] = &$user_id;
            
            $update_client = "UPDATE clients SET " . implode(', ', $update_parts) . " WHERE user_id = ?";
            $stmt = $conn->prepare($update_client);
            
            if ($stmt === false) {
                $error = "Database error: " . $conn->error;
            } else {
                // Bind parameters dynamically
                call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $params));
                
                if ($stmt->execute()) {
                    $success = "Profile updated successfully!";
                    // Refresh user data
                    $stmt = $conn->prepare($query);
                    if ($stmt !== false) {
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $stmt->close();
                    }
                } else {
                    $error = "Failed to update client information.";
                }
            }
        } else {
            $error = "No client columns found in database.";
        }
    } else {
        $error = "Failed to update profile.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_password);
                
                if ($stmt === false) {
                    $error = "Database error: " . $conn->error;
                } elseif ($stmt->bind_param("si", $hashed_password, $user_id) && $stmt->execute()) {
                    $success = "Password changed successfully!";
                    $stmt->close();
                } else {
                    $error = "Failed to change password.";
                }
            } else {
                $error = "New password must be at least 6 characters long.";
            }
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CRI Travels</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #667eea;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background: #667eea;
            color: white;
        }
        
        .content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 5px;
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
        
        .profile-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .profile-info h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            width: 150px;
        }
        
        .info-value {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Profile</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="my_trips.php">My Trips</a>
                <a href="../config/logout.php">Logout</a>
            </div>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="tabs">
                <button class="tab active" onclick="showTab('info')">Profile Information</button>
                <button class="tab" onclick="showTab('edit')">Edit Profile</button>
                <button class="tab" onclick="showTab('password')">Change Password</button>
            </div>
            
            <div id="info-tab" class="tab-content active">
                <div class="profile-info">
                    <h3>Personal Information</h3>
                    <div class="info-item">
                        <div class="info-label">Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Company Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['company_name'] ?? 'Not set'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['address'] ?? 'Not set'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Account Status:</div>
                        <div class="info-value"><?php echo ucfirst($user['status']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since:</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <div id="edit-tab" class="tab-content">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($user['company_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn">Update Profile</button>
                </form>
            </div>
            
            <div id="password-tab" class="tab-content">
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="confirm_password" minlength="6" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn">Change Password</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
