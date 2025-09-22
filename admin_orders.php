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

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    
    if ($new_status) {
        $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            $message = "Order status updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating order status: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$view_order = $_GET['view'] ?? '';

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];
$types = "";

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (customer_name LIKE ? OR customer_email LIKE ? OR order_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders_result = $stmt->get_result();

$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'processing'");
$stats['processing_orders'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'completed'");
$stats['completed_orders'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;


$order_details = null;
$order_items = null;
if ($view_order) {
    $order_id = intval($view_order);
    $order_result = $conn->query("SELECT * FROM orders WHERE order_id = $order_id");
    $order_details = $order_result->fetch_assoc();
    
    if ($order_details) {
        $items_result = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
        $order_items = $items_result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | LUXLIVING Admin</title>
    
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
        .stat-icon.pending { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .stat-icon.processing { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .stat-icon.completed { background: linear-gradient(135deg, #27ae60, #229954); }
        
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
        
        .filter-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .order-detail-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
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
                <a class="nav-link active" href="admin_orders.php">
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
                <h2 class="mb-0">Order Management</h2>
                <p class="text-muted mb-0">Manage customer orders and fulfillment</p>
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

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon total me-3">
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
                        <div class="stat-icon pending me-3">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['pending_orders']; ?></h3>
                            <p class="text-muted mb-0">Pending Orders</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon processing me-3">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['processing_orders']; ?></h3>
                            <p class="text-muted mb-0">Processing</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon completed me-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['completed_orders']; ?></h3>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($view_order && $order_details): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt"></i> Order Details - #<?php echo $order_details['order_id']; ?>
                        </h5>
                        <a href="admin_orders.php" class="btn btn-outline-light btn-admin">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="order-detail-card">
                                <h6><i class="bi bi-person"></i> Customer Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['customer_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?></p>
                            </div>
                            
                            <div class="order-detail-card">
                                <h6><i class="bi bi-geo-alt"></i> Shipping Address</h6>
                                <p><?php echo nl2br(htmlspecialchars($order_details['shipping_address'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="order-detail-card">
                                <h6><i class="bi bi-info-circle"></i> Order Information</h6>
                                <p><strong>Order Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order_details['created_at'])); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order_details['payment_method'])); ?></p>
                                <p><strong>Total Amount:</strong> <span class="text-success fw-bold">$<?php echo number_format($order_details['total_amount'], 2); ?></span></p>
                                
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="order_id" value="<?php echo $order_details['order_id']; ?>">
                                    <div class="d-flex align-items-center">
                                        <label class="form-label me-2 mb-0"><strong>Status:</strong></label>
                                        <select name="new_status" class="form-select me-2" style="width: auto;">
                                            <option value="pending" <?php echo ($order_details['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo ($order_details['status'] ?? 'pending') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo ($order_details['status'] ?? 'pending') === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?php echo ($order_details['status'] ?? 'pending') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo ($order_details['status'] ?? 'pending') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-success btn-admin">
                                            <i class="bi bi-check"></i> Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <?php if ($order_details['order_notes']): ?>
                                <div class="order-detail-card">
                                    <h6><i class="bi bi-chat-text"></i> Order Notes</h6>
                                    <p><?php echo nl2br(htmlspecialchars($order_details['order_notes'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6><i class="bi bi-list-ul"></i> Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-3">Filter Orders</h5>
                            <div class="filter-buttons">
                                <a href="admin_orders.php" class="btn btn-outline-primary <?php echo empty($status_filter) ? 'active' : ''; ?>">
                                    All Orders
                                </a>
                                <a href="admin_orders.php?status=pending" class="btn btn-outline-warning <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                                    Pending
                                </a>
                                <a href="admin_orders.php?status=processing" class="btn btn-outline-info <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">
                                    Processing
                                </a>
                                <a href="admin_orders.php?status=completed" class="btn btn-outline-success <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                                    Completed
                                </a>
                                <a href="admin_orders.php?status=cancelled" class="btn btn-outline-danger <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                                    Cancelled
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <?php if ($status_filter): ?>
                                    <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                                <?php endif; ?>
                                <input type="text" class="form-control me-2" name="search" 
                                       placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary btn-admin">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 
                        <?php 
                        if ($status_filter) echo ucfirst($status_filter) . ' Orders';
                        else echo 'All Orders';
                        ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders_result->num_rows > 0): ?>
                                    <?php while($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                                            <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                            <td>
                                                <?php
                                                $status = $order['status'] ?? 'pending';
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'shipped' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $color = $status_colors[$status] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($status); ?></span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="?view=<?php echo $order['order_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary btn-admin">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-cart-x display-4 text-muted"></i>
                                            <p class="text-muted mt-2">No orders found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
