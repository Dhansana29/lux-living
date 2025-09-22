<?php
session_start();
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

$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

//Low stock products
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10");
$stats['low_stock'] = $result->fetch_assoc()['total'];

//Pending orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

//Recent orders
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");

//Top selling products
$top_products = $conn->query("
    SELECT p.product_name, p.price, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    GROUP BY p.product_id 
    ORDER BY total_sold DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LUXLIVING</title>
    
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
            transition: all 0.3s ease;
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
            justify-content: between;
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
        
        .stat-icon.products { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stat-icon.orders { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .stat-icon.revenue { background: linear-gradient(135deg, #27ae60, #229954); }
        .stat-icon.stock { background: linear-gradient(135deg, #f39c12, #e67e22); }
        
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
        
        .table {
            margin-bottom: 0;
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
        
        .btn-admin {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
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
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
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
                <a class="nav-link active" href="admin_dashboard.php">
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
                <h2 class="mb-0">Dashboard Overview</h2>
                <p class="text-muted mb-0">Welcome back, <?php echo $_SESSION['admin_username']; ?>!</p>
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

        <!--Statistics Cards--->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon products me-3">
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
                        <div class="stat-icon orders me-3">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_orders']; ?></h3>
                            <p class="text-muted mb-0">Total Orders</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue me-3">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stock me-3">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['low_stock']; ?></h3>
                            <p class="text-muted mb-0">Low Stock Items</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!--Recent Orders-->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Recent Orders
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($order = $recent_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo ucfirst($order['status'] ?? 'pending'); ?></span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <a href="admin_orders.php?view=<?php echo $order['order_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary btn-admin">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-cart-x display-4 text-muted"></i>
                                <p class="text-muted mt-2">No orders found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!--Top Selling Products-->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-trophy"></i> Top Selling Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($top_products->num_rows > 0): ?>
                            <?php while($product = $top_products->fetch_assoc()): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                        <small class="text-muted">$<?php echo number_format($product['price'], 2); ?></small>
                                    </div>
                                    <span class="badge bg-success"><?php echo $product['total_sold']; ?> sold</span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-graph-down display-4 text-muted"></i>
                                <p class="text-muted mt-2">No sales data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="admin_products.php?action=add" class="btn btn-success btn-admin w-100">
                                    <i class="bi bi-plus-circle"></i> Add Product
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="admin_orders.php?status=pending" class="btn btn-warning btn-admin w-100">
                                    <i class="bi bi-clock"></i> Pending Orders (<?php echo $stats['pending_orders']; ?>)
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="admin_stock.php?filter=low" class="btn btn-danger btn-admin w-100">
                                    <i class="bi bi-exclamation-triangle"></i> Low Stock (<?php echo $stats['low_stock']; ?>)
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="admin_reports.php" class="btn btn-info btn-admin w-100">
                                    <i class="bi bi-graph-up"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        //Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
        
        //Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

<?php
$conn->close();
?>
