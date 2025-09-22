<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "luxliving";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    // Get order details
    $order_sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();
    $stmt->close();
    
    if ($order) {
        // Get order items
        $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($items_sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $order_items = [];
        while($row = $items_result->fetch_assoc()) {
            $order_items[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | LUXLIVING</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a class="nav-link" href="login_register.php"><i class="bi bi-person-circle"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container my-5 py-5">
        <?php if ($order): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!--Success Message-->
                    <div class="text-center mb-5">
                        <div class="success-icon mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h1 class="text-success mb-3">Order Confirmed!</h1>
                        <p class="lead text-muted">Thank you for your order. We'll send you a confirmation email shortly.</p>
                    </div>

                    <!--Order Details-->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-receipt"></i> Order Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                    <p><strong>Status:</strong> <span class="badge bg-warning"><?php echo ucfirst($order['order_status']); ?></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                    <p><strong>Payment Status:</strong> <span class="badge bg-info"><?php echo ucfirst($order['payment_status']); ?></span></p>
                                    <p><strong>Total Amount:</strong> <span class="fw-bold text-success">$<?php echo number_format($order['total_amount'], 2); ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Customer Info-->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    <?php if ($order['customer_phone']): ?>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Shipping Address:</strong></p>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Order Items-->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-bag-check"></i> Order Items</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($order_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <p class="mb-0">$<?php echo number_format($item['product_price'], 2); ?> each</p>
                                        <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <h5 class="mb-0">Total:</h5>
                                <h5 class="mb-0 text-success">$<?php echo number_format($order['total_amount'], 2); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> What's Next?</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <i class="bi bi-envelope-fill text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Confirmation Email</h6>
                                    <p class="text-muted small">We'll send you a confirmation email with order details.</p>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <i class="bi bi-truck text-warning mb-2" style="font-size: 2rem;"></i>
                                    <h6>Processing</h6>
                                    <p class="text-muted small">We'll prepare your plants for shipping within 1-2 business days.</p>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <i class="bi bi-house-heart text-success mb-2" style="font-size: 2rem;"></i>
                                    <h6>Delivery</h6>
                                    <p class="text-muted small">Your plants will be delivered to your address within 3-5 business days.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Action Buttons-->
                    <div class="text-center">
                        <a href="shop.php" class="btn btn-success btn-lg me-3">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                        <a href="index.php" class="btn btn-outline-success btn-lg">
                            <i class="bi bi-house"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-warning display-1"></i>
                <h3 class="mt-3">Order Not Found</h3>
                <p class="text-muted">The order you're looking for doesn't exist or has been removed.</p>
                <a href="shop.php" class="btn btn-success">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </main>

    <!--Footer Section-->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-bold mb-3 text-uppercase">LuxLiving</h5>
                    <p class="text-white-50">
                        Bringing nature into your home with a curated collection of healthy, happy plants.
                    </p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-bold mb-3 text-uppercase">Quick Links</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none text-white-50">Home</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-decoration-none text-white-50">Shop</a></li>
                        <li class="mb-2"><a href="about.html" class="text-decoration-none text-white-50">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-decoration-none text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-bold mb-3 text-uppercase">Help</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Shipping & Returns</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Plant Care Guides</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h5 class="fw-bold mb-3 text-uppercase">Contact Us</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            <span>Homagama, Sri Lanka</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <span>contact@luxliving.com</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <span>011 234 5678</span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row">
                <div class="col-12 text-center text-white-50">
                    <p class="mb-0">&copy; 2025 LuxLiving. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
