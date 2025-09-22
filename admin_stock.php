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

//Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $new_stock = intval($_POST['new_stock'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_stock') {
        $sql = "UPDATE products SET stock_quantity = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_stock, $product_id);
        
        if ($stmt->execute()) {
            $message = "Stock updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating stock: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if ($filter === 'low') {
    $sql .= " AND stock_quantity < 10";
} elseif ($filter === 'out') {
    $sql .= " AND stock_quantity = 0";
} elseif ($filter === 'high') {
    $sql .= " AND stock_quantity >= 50";
}

if (!empty($search)) {
    $sql .= " AND (product_name LIKE ? OR category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$sql .= " ORDER BY stock_quantity ASC, product_name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products_result = $stmt->get_result();

//Get stock stats
$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10");
$stats['low_stock'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity = 0");
$stats['out_of_stock'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT SUM(stock_quantity) as total FROM products");
$stats['total_inventory'] = $result->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management | LUXLIVING Admin</title>
    
    <!--Bootstrap CSS-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--Bootstrap Icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!--Google Fonts-->
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
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.total { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stat-icon.low { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .stat-icon.out { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .stat-icon.inventory { background: linear-gradient(135deg, #27ae60, #229954); }
        
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
            width: 50px;
            height: 50px;
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
        
        .stock-input {
            width: 80px;
        }
        
        .filter-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
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
                <a class="nav-link" href="admin_products.php">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_orders.php">
                    <i class="bi bi-cart-check"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_stock.php">
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

    <!--Main Content-->
    <div class="main-content">
        <!--op Navbar-->
        <div class="top-navbar">
            <div>
                <h2 class="mb-0">Stock Management</h2>
                <p class="text-muted mb-0">Monitor and update inventory levels</p>
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

        <!--Stock Stat-->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon total me-3">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_products']; ?></h3>
                            <p class="text-muted mb-0">Total Products</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon low me-3">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['low_stock']; ?></h3>
                            <p class="text-muted mb-0">Low Stock (< 10)</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon out me-3">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['out_of_stock']; ?></h3>
                            <p class="text-muted mb-0">Out of Stock</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon inventory me-3">
                            <i class="bi bi-stack"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_inventory']; ?></h3>
                            <p class="text-muted mb-0">Total Inventory</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Filters and Search-->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-3">Filter Products</h5>
                        <div class="filter-buttons">
                            <a href="admin_stock.php" class="btn btn-outline-primary <?php echo empty($filter) ? 'active' : ''; ?>">
                                All Products
                            </a>
                            <a href="admin_stock.php?filter=low" class="btn btn-outline-warning <?php echo $filter === 'low' ? 'active' : ''; ?>">
                                Low Stock
                            </a>
                            <a href="admin_stock.php?filter=out" class="btn btn-outline-danger <?php echo $filter === 'out' ? 'active' : ''; ?>">
                                Out of Stock
                            </a>
                            <a href="admin_stock.php?filter=high" class="btn btn-outline-success <?php echo $filter === 'high' ? 'active' : ''; ?>">
                                High Stock
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <?php if ($filter): ?>
                                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                            <?php endif; ?>
                            <input type="text" class="form-control me-2" name="search" 
                                   placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary btn-admin">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--Stock Management Table-->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> 
                    <?php 
                    if ($filter === 'low') echo 'Low Stock Products';
                    elseif ($filter === 'out') echo 'Out of Stock Products';
                    elseif ($filter === 'high') echo 'High Stock Products';
                    else echo 'All Products';
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Update Stock</th>
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
                                            <br><small class="text-muted">ID: <?php echo $product['product_id']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($product['category']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock_quantity'] == 0 ? 'danger' : ($product['stock_quantity'] < 10 ? 'warning' : 'success'); ?> fs-6">
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td><strong>$<?php echo number_format($product['price'], 2); ?></strong></td>
                                        <td>
                                            <?php if ($product['stock_quantity'] == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($product['stock_quantity'] < 10): ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="action" value="update_stock">
                                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                <input type="number" class="form-control stock-input me-2" 
                                                       name="new_stock" value="<?php echo $product['stock_quantity']; ?>" 
                                                       min="0" required>
                                                <button type="submit" class="btn btn-sm btn-success btn-admin">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
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
        //Auto-submit form when stock input changes
        document.querySelectorAll('input[name="new_stock"]').forEach(input => {
            input.addEventListener('change', function() {
                if (confirm('Are you sure you want to update the stock for this product?')) {
                    this.form.submit();
                } else {
                    this.value = this.defaultValue;
                }
            });
        });
        
        document.querySelectorAll('.filter-buttons .btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                document.querySelectorAll('.filter-buttons .btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
