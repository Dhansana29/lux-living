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
    <title>Contact Us | LUXLIVING</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <link rel="stylesheet" href="style.css">
    
    <link rel="stylesheet" href="contact.css">
    
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
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
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
    
    <main>
        <section class="contact-section py-5">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="contact-info p-4 p-md-5 rounded shadow-sm animate__animated animate__fadeInLeft">
                            <h2 class="section-title mb-4">Get in Touch</h2>
                            <p class="mb-4">
                                Have a question or need to get in touch? Fill out the form below or contact us directly. We'd love to hear from you!
                            </p>
                            
                            <div class="business-details mb-4">
                                <ul class="list-unstyled">
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-geo-alt-fill me-3 fs-4 text-success"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Address</h6>
                                            <p class="mb-0 text-muted">Homagama, Sri Lanka</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-envelope-fill me-3 fs-4 text-success"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Email</h6>
                                            <p class="mb-0 text-muted">contact@luxliving.com</p>
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start mb-3">
                                        <i class="bi bi-telephone-fill me-3 fs-4 text-success"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Phone</h6>
                                            <p class="mb-0 text-muted">011 234 5678</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="social-links">
                                <a href="#" class="me-3 text-success fs-4"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="me-3 text-success fs-4"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="me-3 text-success fs-4"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="me-3 text-success fs-4"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="contact-form-container p-4 p-md-5 rounded shadow-sm animate__animated animate__fadeInRight">
                            <h3 class="mb-4 fw-bold">Send us a message</h3>
                            <form id="contact-form" action="handle_contact.php" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Send Message</button>
                                <div id="form-message" class="mt-3 text-center d-none"></div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="map-container rounded shadow-sm animate__animated animate__fadeInUp">
                            <h3 class="mb-4 text-center fw-bold">Our Location</h3>
                            <div id="map" class="map"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <h5 class="fw-bold mb-3 text-uppercase">LuxLiving</h5>
                    <p class="text-white-50">
                        We are dedicated to bringing the beauty of nature into your home with our wide selection of plants and accessories.
                    </p>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <h5 class="fw-bold mb-3 text-uppercase">Quick Links</h5>
                    <ul class="list-unstyled text-white-50">
                        <li><a href="#" class="text-decoration-none text-white-50">Shop Now</a></li>
                        <li><a href="about.html" class="text-decoration-none text-white-50">About Us</a></li>
                        <li><a href="contact.php" class="text-decoration-none text-white-50">Contact Us</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50">FAQs</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <h5 class="fw-bold mb-3 text-uppercase">Support</h5>
                    <ul class="list-unstyled text-white-50">
                        <li><a href="#" class="text-decoration-none text-white-50">Shipping & Returns</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50">Privacy Policy</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50">Terms of Service</a></li>
                        <li><a href="#" class="text-decoration-none text-white-50">Accessibility</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
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
    
    <script src="script.js"></script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY_HERE&callback=initMap"></script>
    
    <script src="contact.js"></script>

</body>
</html>