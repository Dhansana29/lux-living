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
    <title>LUXLIVING | Your Online Plant Store</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- CSS File -->
    <link rel="stylesheet" href="style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
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
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
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
                    <a class="nav-link" href="cart.php"><i class="bi bi-cart3"></i></a>
                    <a class="nav-link" href="#"><i class="bi bi-person-circle"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-text">
                    <h1>Bring Nature Into<br>Your <span class="highlight">Home</span></h1>
                    <p>Find the perfect plant for your space. We deliver healthy, happy plants to your door.</p>
                    <a href="#" class="btn btn-primary">Shop All Plants <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="col-lg-6 hero-image-container">
                    <img src="https://images.unsplash.com/photo-1463320898484-cdee8141c787?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Beautiful Potted Plant" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
      <div class="container">
          <div class="row text-center">
              <div class="col-md-4">
                  <h2 data-target="10000">0</h2>
                  <p>Happy Customers</p>
              </div>
              <div class="col-md-4">
                  <h2 data-target="500">0</h2>
                  <p>Plant Varieties</p>
              </div>
              <div class="col-md-4">
                  <h2 data-target="5">0</h2>
                  <p>Years of Experience</p>
              </div>
          </div>
      </div>
    </section>

    <!--Why choose us section-->
    <section class="why-choose-us py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Us?</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
					<div class="card h-100 p-4">
                        <div class="icon-container mb-3">
                            <i class="bi bi-truck-flatbed"></i>
                        </div>
                        <h5 class="card-title">Fast & Secure Delivery</h5>
                        <p class="card-text">We ensure your plants arrive quickly and in perfect condition, ready to thrive in their new home.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
					<div class="card h-100 p-4">
                        <div class="icon-container mb-3">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <h5 class="card-title">Healthy, Happy Plants</h5>
                        <p class="card-text">Our plants are nurtured with care by expert botanists, guaranteeing vibrant and long-lasting greenery.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
					<div class="card h-100 p-4">
                        <div class="icon-container mb-3">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h5 class="card-title">Expert Customer Support</h5>
                        <p class="card-text">Our team of plant lovers is always here to help with any questions, from care tips to finding the perfect plant.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--Featured Products Section-->
    <section class="featured-products py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Featured Plants</h2>
            <div class="row">
                <?php
                $sql = "SELECT product_id, product_name, price, image_url FROM products WHERE is_featured = 1 LIMIT 3";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '
                        <div class="col-md-4 mb-4">
                            <div class="product-card">
                                <img src="' . $row["image_url"]. '" alt="' . $row["product_name"]. '" class="img-fluid rounded">
                                <div class="card-body text-center mt-3">
                                    <h5 class="card-title">' . $row["product_name"]. '</h5>
                                    <p class="card-text">$' . number_format($row["price"], 2) . '</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-outline-success add-to-cart" data-product-id="' . $row["product_id"]. '">
                                            <i class="bi bi-bag-plus"></i> Add to Cart
                                        </button>
                                        <button class="btn btn-outline-primary add-to-wishlist" data-product-id="' . $row["product_id"]. '">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo "No featured products found.";
                }
                ?>
            </div>
        </div>
    </section>

    <!--Testimonials Section-->
    <section class="testimonials-section py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold animate__animated animate__fadeInUp">What Our Customers Say</h2>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="testimonial-card shadow-sm p-4 text-center">
                            <i class="bi bi-quote quote-icon mb-3"></i>
                            <p class="mb-4 testimonial-text">"LuxLiving has completely transformed my home. The quality of their plants is unmatched, and the delivery was incredibly fast. I'm so happy with my new fiddle-leaf fig!"</p>
                            <div class="customer-info">
                                <img src="https://images.unsplash.com/photo-1600486913747-55e5470d6f40?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Customer Jane Doe" class="rounded-circle mb-2">
                                <h5 class="fw-bold">Jane Doe</h5>
                                <p class="text-muted">Plant Enthusiast</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial-card shadow-sm p-4 text-center">
                            <i class="bi bi-quote quote-icon mb-3"></i>
                            <p class="mb-4 testimonial-text">"I've been looking for a reliable source for rare plants, and LuxLiving delivered. The ordering process was simple, and the plant arrived in perfect condition. Highly recommend!"</p>
                            <div class="customer-info">
                                <img src="https://images.unsplash.com/photo-1522529599102-193c0d76b5b6?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Customer John Smith" class="rounded-circle mb-2">
                                <h5 class="fw-bold">John Smith</h5>
                                <p class="text-muted">Interior Designer</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="testimonial-card shadow-sm p-4 text-center">
                            <i class="bi bi-quote quote-icon mb-3"></i>
                            <p class="mb-4 testimonial-text">"Great selection and great service! My Monstera is thriving thanks to the care tips included. This is my new go-to place for all my plant needs."</p>
                            <div class="customer-info">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Customer Sarah Connor" class="rounded-circle mb-2">
                                <h5 class="fw-bold">Sarah Connor</h5>
                                <p class="text-muted">Home Decorator</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    
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