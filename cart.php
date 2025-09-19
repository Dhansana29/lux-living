<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "luxliving";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | LUXLIVING</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
</head>
<body>

    <nav id="navbar" class="navbar navbar-expand-lg navbar-light bg-transparent fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">LUXLIVING</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
                <div class="navbar-nav navbar-icons">
                    <a class="nav-link" href="#"><i class="bi bi-search"></i></a>
                    <a class="nav-link active" href="cart.php"><i class="bi bi-cart3"></i></a>
                    <a class="nav-link" href="#"><i class="bi bi-person-circle"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container my-5 py-5">
        <h1 class="text-center mb-5">Your Shopping Cart</h1>
        <div class="row" id="cart-container">
            <?php
            $total_price = 0;
            $sql = "SELECT p.product_id, p.product_name, p.price, p.image_url, ci.quantity FROM cart_items ci JOIN products p ON ci.product_id = p.product_id ORDER BY ci.created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $item_total = $row["price"] * $row["quantity"];
                    $total_price += $item_total;
                    echo '
                    <div class="col-12 mb-4 cart-item" data-product-id="' . $row["product_id"] . '">
                        <div class="card p-3 shadow-sm rounded-3">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-2">
                                    <img src="' . $row["image_url"] . '" class="img-fluid rounded-start cart-image" alt="' . $row["product_name"] . '">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title mb-1">' . $row["product_name"] . '</h5>
                                        <p class="card-text text-success fw-bold">$' . number_format($row["price"], 2) . '</p>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex justify-content-center align-items-center">
                                    <div class="input-group quantity-control">
                                        <button class="btn btn-outline-secondary btn-decrease" type="button" data-product-id="' . $row["product_id"] . '">-</button>
                                        <input type="text" class="form-control text-center quantity-input" value="' . $row["quantity"] . '" readonly>
                                        <button class="btn btn-outline-secondary btn-increase" type="button" data-product-id="' . $row["product_id"] . '">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <p class="fw-bold fs-5 mb-0 item-total">$' . number_format($item_total, 2) . '</p>
                                    <button class="btn btn-link text-danger remove-item" data-product-id="' . $row["product_id"] . '">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info text-center" role="alert">Your cart is empty.</div></div>';
            }
            ?>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="row justify-content-end my-4">
            <div class="col-md-4">
                <div class="card p-4 shadow-sm">
                    <h4 class="card-title text-center mb-3">Cart Summary</h4>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Subtotal: <span class="fw-bold" id="cart-subtotal">$<?php echo number_format($total_price, 2); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Shipping: <span class="text-muted">Free</span>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5" id="cart-total">$<?php echo number_format($total_price, 2); ?></span>
                    </div>
                    <button class="btn btn-success btn-lg mt-4">Proceed to Checkout</button>
                </div>
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
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Contact</a></li>
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
                            <span>123 Plant Street, Green City, 45678</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <span>info@luxliving.com</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <span>+1 234 567 890</span>
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
    <script src="script.js"></script>
</body>
</html>
<?php
$conn->close();
?>