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

//Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $shipping_address = $_POST['shipping_address'];
    $billing_address = $_POST['billing_address'] ?: $shipping_address;
    $payment_method = $_POST['payment_method'];
    $order_notes = $_POST['order_notes'];
    
    //Get cart items
    $cart_sql = "SELECT p.product_id, p.product_name, p.price, ci.quantity 
                 FROM cart_items ci 
                 JOIN products p ON ci.product_id = p.product_id";
    $cart_result = $conn->query($cart_sql);
    
    if ($cart_result->num_rows > 0) {
        //Calculate total
        $total_amount = 0;
        $cart_items = [];
        while($row = $cart_result->fetch_assoc()) {
            $subtotal = $row['price'] * $row['quantity'];
            $total_amount += $subtotal;
            $cart_items[] = $row;
        }
        
        $conn->begin_transaction();
        
        try {
            $order_sql = "INSERT INTO orders (customer_name, customer_email, customer_phone, shipping_address, billing_address, total_amount, payment_method, order_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($order_sql);
            $stmt->bind_param("sssssdss", $customer_name, $customer_email, $customer_phone, $shipping_address, $billing_address, $total_amount, $payment_method, $order_notes);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($item_sql);
            
            foreach ($cart_items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bind_param("iisidi", $order_id, $item['product_id'], $item['product_name'], $item['price'], $item['quantity'], $subtotal);
                $stmt->execute();
            }
            $stmt->close();
            
            $clear_cart_sql = "DELETE FROM cart_items";
            $conn->query($clear_cart_sql);
            
            $conn->commit();
            
            header("Location: order_success.php?order_id=" . $order_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Order failed: " . $e->getMessage();
        }
    } else {
        $error_message = "Your cart is empty!";
    }
}

$cart_sql = "SELECT p.product_id, p.product_name, p.price, p.image_url, ci.quantity 
             FROM cart_items ci 
             JOIN products p ON ci.product_id = p.product_id";
$cart_result = $conn->query($cart_sql);

$total_amount = 0;
$cart_items = [];
if ($cart_result->num_rows > 0) {
    while($row = $cart_result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        $total_amount += $subtotal;
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | LUXLIVING</title>
    
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
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-5">Checkout</h1>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <h3 class="mt-3">Your cart is empty</h3>
                <p class="text-muted">Add some plants to your cart before checking out.</p>
                <a href="shop.php" class="btn btn-success">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!--Order Summary-->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-bag-check"></i> Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                        </div>
                                    </div>
                                    <span class="fw-bold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold text-success fs-5">$<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Checkout Form-->
                <div class="col-lg-8">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                        <div class="invalid-feedback">
                                            Please provide your full name.
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="customer_phone" name="customer_phone">
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Shipping Address</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Address *</label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required placeholder="Enter your complete shipping address"></textarea>
                                    <div class="invalid-feedback">
                                        Please provide your shipping address.
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="same_billing">
                                    <label class="form-check-label" for="same_billing">
                                        Billing address is the same as shipping address
                                    </label>
                                </div>
                                <div class="mb-3 mt-3" id="billing_address_section" style="display: none;">
                                    <label for="billing_address" class="form-label">Billing Address</label>
                                    <textarea class="form-control" id="billing_address" name="billing_address" rows="3" placeholder="Enter your billing address (if different)"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method *</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select payment method</option>
                                        <option value="cash_on_delivery">Cash on Delivery</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="credit_card">Credit Card</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a payment method.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="order_notes" class="form-label">Order Notes</label>
                                    <textarea class="form-control" id="order_notes" name="order_notes" rows="3" placeholder="Any special instructions for your order?"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-check-circle"></i> Place Order
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary btn-lg ms-3">
                                <i class="bi bi-arrow-left"></i> Back to Cart
                            </a>
                        </div>
                    </form>
                </div>
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
    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        document.getElementById('same_billing').addEventListener('change', function() {
            const billingSection = document.getElementById('billing_address_section');
            const billingAddress = document.getElementById('billing_address');
            
            if (this.checked) {
                billingSection.style.display = 'none';
                billingAddress.value = '';
            } else {
                billingSection.style.display = 'block';
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>

