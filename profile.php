<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login_register.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "luxliving";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$email = '';
$profile_picture = '';
$update_message = '';

$sql = "SELECT full_name, email, profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $full_name = $user['full_name'];
    $email = $user['email'];
    $profile_picture = $user['profile_picture'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_full_name = trim($_POST['full_name']);

    if (!empty($new_full_name)) {
        $update_sql = "UPDATE users SET full_name = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if ($update_stmt) {
            $update_stmt->bind_param("si", $new_full_name, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['full_name'] = $new_full_name;
                $full_name = $new_full_name;
                $update_message = "Profile updated successfully! ✅";
            } else {
                $update_message = "Failed to update profile. ❌";
            }
            $update_stmt->close();
        } else {
            $update_message = "Error preparing update statement: " . $conn->error;
        }
    }
}

$orders_sql = "
    SELECT 
        o.order_id, 
        o.status, 
        o.total_amount, 
        o.payment_method, 
        oi.product_id,
        oi.product_name
    FROM 
        orders o
    JOIN 
        order_items oi ON o.order_id = oi.order_id
    WHERE 
        o.customer_name = ?
    ORDER BY 
        o.created_at DESC";

$orders_stmt = $conn->prepare($orders_sql);

if ($orders_stmt) {
    $orders_stmt->bind_param("s", $full_name);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $orders_stmt->close();
} else {
    $orders_result = false;
    error_log("Error preparing orders statement: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | LUXLIVING</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            margin-top: 80px;
            padding: 40px 0;
        }
        .profile-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #2d6a4f;
            padding: 3px;
        }
        .profile-name-input {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d6a4f;
            border: none;
            background-color: transparent;
        }
        .order-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .order-status-badge {
            font-weight: 600;
        }
    </style>
</head>
<body>

    <nav id="navbar" class="navbar navbar-expand-lg navbar-light bg-transparent fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">LUXLIVING</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <div class="navbar-nav navbar-icons">
                    <a class="nav-link" href="#"><i class="bi bi-search"></i></a>
                    <a class="nav-link" href="cart.php"><i class="bi bi-cart3"></i></a>
                    <a class="nav-link" href="wishlist.php"><i class="bi bi-heart"></i></a>
                    <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container profile-container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="profile-card">
                    <div class="d-flex align-items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1728577740843-5f29c7586afe?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Profile Picture" class="profile-picture me-4">
                        <div>
                            <form action="profile.php" method="POST" class="d-flex align-items-center">
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" class="profile-name-input me-2">
                                <button type="submit" name="update_profile" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></button>
                            </form>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($email); ?></p>
                        </div>
                    </div>

                    <?php if ($update_message): ?>
                        <div class="alert alert-info" role="alert"><?php echo $update_message; ?></div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <h4 class="mb-3 text-success fw-bold">Active Orders</h4>
                    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while($order = $orders_result->fetch_assoc()): ?>
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center flex-wrap mb-2 order-card">
                                    <div class="me-auto p-2">
                                        <strong>Order #<?php echo htmlspecialchars($order['order_id']); ?></strong>
                                        <p class="mb-0 text-muted">Product: <?php echo htmlspecialchars($order['product_name']); ?> (ID: <?php echo htmlspecialchars($order['product_id']); ?>)</p>
                                    </div>
                                    <div class="p-2">
                                        <p class="mb-0">
                                            Status: <span class="badge bg-secondary rounded-pill order-status-badge"><?php echo htmlspecialchars($order['status']); ?></span>
                                        </p>
                                    </div>
                                    <div class="p-2">
                                        <p class="mb-0">
                                            Payment: <span class="badge bg-info text-dark rounded-pill"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                        </p>
                                    </div>
                                    <div class="p-2">
                                        <p class="mb-0 text-success fw-bold">Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">You have no active orders.</div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <h4 class="mb-3 text-success fw-bold">Settings</h4>
                    <div class="alert alert-light">
                        <p class="mb-0">This is a placeholder for future settings like changing password or managing preferences.</p>
                    </div>

                    <hr class="my-4">

                    <a href="logout.php" class="btn btn-danger w-100">Sign Out <i class="bi bi-box-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>