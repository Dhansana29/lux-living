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

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 1000;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';

$sql = "SELECT product_id, product_name, price, image_url, description, category, stock_quantity, rating FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (product_name LIKE ? OR description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= "ss";
}

$sql .= " AND price BETWEEN ? AND ?";
$params[] = $price_min;
$params[] = $price_max;
$types .= "dd";

// Add sorting
switch($sort_by) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY rating DESC";
        break;
    case 'name':
    default:
        $sql .= " ORDER BY product_name ASC";
        break;
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories_sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category";
$categories_result = $conn->query($categories_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Plants | LUXLIVING</title>
    
    <!--Bootstrap CSS-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--Bootstrap Icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!--Animate.css-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!--CSS File-->
    <link rel="stylesheet" href="style.css">
    <!--Google Fonts-->
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
                        <a class="nav-link active" href="shop.php">Shop</a>
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

    <!--Shop Hero Section-->
    <section class="shop-hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="shop-hero-title animate__animated animate__fadeInUp">Discover Your Perfect Plant</h1>
                    <p class="shop-hero-subtitle animate__animated animate__fadeInUp animate__delay-1s">
                        From lush tropicals to elegant succulents, find the perfect green companion for your space
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!--Shop Content-->
    <section class="shop-content py-5">
        <div class="container">
            <div class="row">
                <!--Sidebar Filters-->
                <div class="col-lg-3 mb-4">
                    <div class="shop-sidebar">
                        <h4 class="sidebar-title">Filter & Sort</h4>
                        
                        <!--Search-->
                        <div class="filter-section">
                            <h5>Search Plants</h5>
                            <form method="GET" class="search-form">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Search plants..." value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button class="btn btn-outline-success" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>

                                <?php if($category_filter): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                                <?php endif; ?>
                                <?php if($price_min > 0): ?>
                                    <input type="hidden" name="price_min" value="<?php echo $price_min; ?>">
                                <?php endif; ?>
                                <?php if($price_max < 1000): ?>
                                    <input type="hidden" name="price_max" value="<?php echo $price_max; ?>">
                                <?php endif; ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                            </form>
                        </div>

                        <!--Category Filter-->
                        <div class="filter-section">
                            <h5>Category</h5>
                            <div class="category-filters">
                                <a href="?" class="category-filter <?php echo empty($category_filter) ? 'active' : ''; ?>">
                                    All Plants
                                </a>
                                <?php while($category = $categories_result->fetch_assoc()): ?>
                                    <a href="?category=<?php echo urlencode($category['category']); ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?><?php echo $price_min > 0 ? '&price_min=' . $price_min : ''; ?><?php echo $price_max < 1000 ? '&price_max=' . $price_max : ''; ?>&sort=<?php echo urlencode($sort_by); ?>" 
                                       class="category-filter <?php echo $category_filter === $category['category'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['category']); ?>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!--Price Range-->
                        <div class="filter-section">
                            <h5>Price Range</h5>
                            <form method="GET" class="price-form">
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="price_min" placeholder="Min" value="<?php echo $price_min; ?>" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="price_max" placeholder="Max" value="<?php echo $price_max; ?>" min="0">
                                    </div>
                                </div>

                                <?php if($category_filter): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                                <?php endif; ?>
                                <?php if($search_query): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                                <button type="submit" class="btn btn-success btn-sm mt-2 w-100">Apply</button>
                            </form>
                        </div>

                        <!--Sort-->
                        <div class="filter-section">
                            <h5>Sort By</h5>
                            <form method="GET" class="sort-form">
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                                    <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                </select>

                                <?php if($category_filter): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
                                <?php endif; ?>
                                <?php if($search_query): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                                <?php endif; ?>
                                <?php if($price_min > 0): ?>
                                    <input type="hidden" name="price_min" value="<?php echo $price_min; ?>">
                                <?php endif; ?>
                                <?php if($price_max < 1000): ?>
                                    <input type="hidden" name="price_max" value="<?php echo $price_max; ?>">
                                <?php endif; ?>
                            </form>
                        </div>

                        <!--Clear Filters-->
                        <div class="filter-section">
                            <a href="shop.php" class="btn btn-outline-secondary w-100">Clear All Filters</a>
                        </div>
                    </div>
                </div>

                <!--Products Grid-->
                <div class="col-lg-9">
                    <div class="shop-header mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h3 class="shop-results-title">
                                    <?php 
                                    $total_products = $result->num_rows;
                                    if ($total_products > 0) {
                                        echo $total_products . " Plant" . ($total_products > 1 ? 's' : '') . " Found";
                                    } else {
                                        echo "No Plants Found";
                                    }
                                    ?>
                                </h3>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="view-toggle">
                                    <button class="btn btn-outline-success active" id="grid-view">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                    </button>
                                    <button class="btn btn-outline-success" id="list-view">
                                        <i class="bi bi-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="products-grid" id="products-container">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="product-card shop-product-card" data-product-id="<?php echo $row['product_id']; ?>">
                                    <div class="product-image-container">
                                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                                        <div class="product-overlay">
                                            <button class="btn btn-success btn-sm quick-view" data-product-id="<?php echo $row['product_id']; ?>">
                                                <i class="bi bi-eye"></i> Quick View
                                            </button>
                                        </div>
                                        <?php if($row['stock_quantity'] <= 0): ?>
                                            <div class="out-of-stock-badge">Out of Stock</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-category"><?php echo htmlspecialchars($row['category']); ?></div>
                                        <h5 class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></h5>
                                        <?php if($row['description']): ?>
                                            <p class="product-description"><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?></p>
                                        <?php endif; ?>
                                        <div class="product-rating">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $row['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="rating-text">(<?php echo number_format($row['rating'], 1); ?>)</span>
                                        </div>
                                        <div class="product-price">$<?php echo number_format($row['price'], 2); ?></div>
                                        <div class="product-actions">
                                            <?php if($row['stock_quantity'] > 0): ?>
                                                <button class="btn btn-success add-to-cart" data-product-id="<?php echo $row['product_id']; ?>">
                                                    <i class="bi bi-bag-plus"></i> Add to Cart
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="bi bi-x-circle"></i> Out of Stock
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-primary add-to-wishlist" data-product-id="<?php echo $row['product_id']; ?>">
                                                <i class="bi bi-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-products-found">
                                <div class="text-center py-5">
                                    <i class="bi bi-search display-1 text-muted"></i>
                                    <h3 class="mt-3">No plants found</h3>
                                    <p class="text-muted">Try adjusting your filters or search terms</p>
                                    <a href="shop.php" class="btn btn-success">View All Plants</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!--Load More Button-->
                    <?php if ($result->num_rows >= 12): ?>
                        <div class="text-center mt-5">
                            <button class="btn btn-outline-success btn-lg" id="load-more">
                                <i class="bi bi-arrow-down-circle"></i> Load More Plants
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!--Quick View Model-->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickViewModalLabel">Plant Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quickViewContent">
                </div>
            </div>
        </div>
    </div>

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
                            <span>1Homagama, Sri Lanka</span>
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
    <script src="shop.js"></script>
</body>
</html>

<?php
$conn->close();
?>
