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

// Function to remove item from wishlist
if (isset($_POST['remove_from_wishlist'])) {
    $product_id = $_POST['product_id'];
    $sql_remove = "DELETE FROM wishlist_items WHERE product_id = ?";
    $stmt = $conn->prepare($sql_remove);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    header("Location: wishlist.php"); // Redirect to refresh the page
    exit();
}

// Fetch wishlist items
$sql_wishlist = "
    SELECT p.product_id, p.product_name, p.price, p.image_url, p.description
    FROM wishlist_items wi
    JOIN products p ON wi.product_id = p.product_id
";
$result_wishlist = $conn->query($sql_wishlist);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | LUXLIVING</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link rel="stylesheet" href="style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a class="nav-link" href="login_register.php"><i class="bi bi-person-circle"></i></a>
                </div>
            </div>
        </div>
    </nav>
    
    <section class="wishlist-section py-5">
        <div class="container">
            <h1 class="text-center mb-5 wishlist-title">My Wishlist 🌱</h1>
            <div class="row">
                <?php
                if ($result_wishlist->num_rows > 0) {
                    while($row = $result_wishlist->fetch_assoc()) {
                        echo '
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="wishlist-product-card h-100">
                                <img src="' . htmlspecialchars($row["image_url"]) . '" class="card-img-top wishlist-img" alt="' . htmlspecialchars($row["product_name"]) . '">
                                <div class="card-body text-center">
                                    <h5 class="card-title product-name">' . htmlspecialchars($row["product_name"]) . '</h5>
                                    <p class="card-text product-price">$' . number_format($row["price"], 2) . '</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-outline-success add-to-cart" data-product-id="' . $row["product_id"] . '">
                                            <i class="bi bi-bag-plus"></i> Add to Cart
                                        </button>
                                        <form method="post" action="wishlist.php" class="d-inline">
                                            <input type="hidden" name="product_id" value="' . $row["product_id"] . '">
                                            <button type="submit" name="remove_from_wishlist" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '
                    <div class="col-12 text-center py-5">
                        <p class="text-muted fs-4">Your wishlist is empty. Start adding some plants! 🌿</p>
                        <a href="shop.php" class="btn btn-primary mt-3">Browse Plants</a>
                    </div>';
                }
                ?>
            </div>
        </div>
    </section>

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
                            <span>Homagama, Sri Lanka</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <span>contact@luxliving.com</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <span>011 234 5678 </span>
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