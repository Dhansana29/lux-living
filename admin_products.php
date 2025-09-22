<?php
session_start();

//Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "luxliving";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $product_name = $_POST['product_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? '';
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $image_url = $_POST['image_url'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $rating = floatval($_POST['rating'] ?? 0);
        
        $sql = "INSERT INTO products (product_name, description, price, category, stock_quantity, image_url, is_featured, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsisid", $product_name, $description, $price, $category, $stock_quantity, $image_url, $is_featured, $rating);
        
        if ($stmt->execute()) {
            $message = "Product added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding product: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
        
    } elseif ($action === 'edit') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $product_name = $_POST['product_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? '';
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $image_url = $_POST['image_url'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $rating = floatval($_POST['rating'] ?? 0);
        
        $sql = "UPDATE products SET product_name=?, description=?, price=?, category=?, stock_quantity=?, image_url=?, is_featured=?, rating=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsisidi", $product_name, $description, $price, $category, $stock_quantity, $image_url, $is_featured, $rating, $product_id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating product: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting product: " . $stmt->error;
        $message_type = "danger";
    }
    $stmt->close();
}

//Get products for display
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (product_name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

$sql .= " ORDER BY product_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products_result = $stmt->get_result();

//Get categories for filter
$categories_result = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");

//Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM products WHERE product_id = $edit_id");
    $edit_product = $edit_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | LUXLIVING Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 1rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border-left: 4px solid #3498db;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .btn-admin {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
    </style>
</head>
<body>
    <!--Sidebar-->
    <nav class="sidebar">
        <div class="p-3">
            <h4 class="text-white mb-0">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </h4>
            <small class="text-muted">LUXLIVING Store</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin_dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_products.php">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_orders.php">
                    <i class="bi bi-cart-check"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_stock.php">
                    <i class="bi bi-boxes"></i> Stock Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_customers.php">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_reports.php">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link" href="admin_settings.php">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
        
        <div class="mt-auto p-3">
            <a href="admin_logout.php" class="logout-btn d-block text-center">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="top-navbar">
            <div>
                <h2 class="mb-0">Product Management</h2>
                <p class="text-muted mb-0">Manage your plant inventory</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-primary btn-admin me-2">
                    <i class="bi bi-house"></i> View Site
                </a>
                <a href="admin_logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!--Add/Edit Product Form-->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $edit_product ? 'pencil' : 'plus-circle'; ?>"></i>
                    <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="product_name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" 
                                   value="<?php echo $edit_product['product_name'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Indoor Plants" <?php echo ($edit_product['category'] ?? '') === 'Indoor Plants' ? 'selected' : ''; ?>>Indoor Plants</option>
                                <option value="Outdoor Plants" <?php echo ($edit_product['category'] ?? '') === 'Outdoor Plants' ? 'selected' : ''; ?>>Outdoor Plants</option>
                                <option value="Succulents" <?php echo ($edit_product['category'] ?? '') === 'Succulents' ? 'selected' : ''; ?>>Succulents</option>
                                <option value="Flowering Plants" <?php echo ($edit_product['category'] ?? '') === 'Flowering Plants' ? 'selected' : ''; ?>>Flowering Plants</option>
                                <option value="Herbs" <?php echo ($edit_product['category'] ?? '') === 'Herbs' ? 'selected' : ''; ?>>Herbs</option>
                                <option value="Trees" <?php echo ($edit_product['category'] ?? '') === 'Trees' ? 'selected' : ''; ?>>Trees</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_product['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?php echo $edit_product['price'] ?? ''; ?>" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                   value="<?php echo $edit_product['stock_quantity'] ?? ''; ?>" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="rating" class="form-label">Rating (0-5)</label>
                            <input type="number" class="form-control" id="rating" name="rating" 
                                   value="<?php echo $edit_product['rating'] ?? ''; ?>" step="0.1" min="0" max="5">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Featured Product</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                       <?php echo ($edit_product['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
                                    Show on homepage
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL *</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" 
                               value="<?php echo $edit_product['image_url'] ?? ''; ?>" required>
                        <div class="form-text">Enter a valid image URL (e.g., from Unsplash)</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-admin">
                            <i class="bi bi-<?php echo $edit_product ? 'check' : 'plus'; ?>"></i>
                            <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                        </button>
                        <?php if ($edit_product): ?>
                            <a href="admin_products.php" class="btn btn-secondary btn-admin">
                                <i class="bi bi-x"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!--Products List-->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> All Products
                    </h5>
                    <div class="d-flex gap-2">
                        <form method="GET" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" 
                                   placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            <select name="category" class="form-select me-2">
                                <option value="">All Categories</option>
                                <?php while($cat = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['category']; ?>" 
                                            <?php echo $category_filter === $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['category']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-primary btn-admin">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Rating</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($products_result->num_rows > 0): ?>
                                <?php while($product = $products_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo $product['image_url']; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                                 class="product-image">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                            <?php if ($product['description']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($product['category']); ?></span>
                                        </td>
                                        <td><strong>$<?php echo number_format($product['price'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock_quantity'] < 10 ? 'danger' : ($product['stock_quantity'] < 20 ? 'warning' : 'success'); ?>">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-star-fill text-warning me-1"></i>
                                                <?php echo number_format($product['rating'], 1); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($product['is_featured']): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?edit=<?php echo $product['product_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary btn-admin">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=<?php echo $product['product_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger btn-admin"
                                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-box display-4 text-muted"></i>
                                        <p class="text-muted mt-2">No products found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
    </script>
</body>
</html>

<?php
$conn->close();
?>
