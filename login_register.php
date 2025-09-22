<?php
session_start();
// Redirect to profile page if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "luxliving";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';
$active_tab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $active_tab = 'login';
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    // Check if user exists
    $sql = "SELECT user_id, full_name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: profile.php');
            exit;
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "User not found. Please register first.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $active_tab = 'register';
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    // Check if email already exists
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "This email is already registered. Please login.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $full_name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Set session and redirect to profile page
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['full_name'] = $full_name;
            header('Location: profile.php');
            exit;
        } else {
            $error_message = "Registration failed. Please try again.";
        }
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUXLIVING | Login/Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
        }
        .auth-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }
        .nav-tabs .nav-link {
            color: #6c757d;
        }
        .nav-tabs .nav-link.active {
            color: #2d6a4f;
            border-color: #2d6a4f #2d6a4f #fff;
        }
        .form-control:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 0.25rem rgba(45, 106, 79, 0.25);
        }
    </style>
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
                    <a class="nav-link active" href="login_register.php"><i class="bi bi-person-circle"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container auth-container">
        <h2 id="auth-title" class="text-center mb-4 text-success fw-bold">Welcome Back!</h2>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'login') ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="<?php echo ($active_tab === 'login') ? 'true' : 'false'; ?>">Login</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($active_tab === 'register') ? 'active' : ''; ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="<?php echo ($active_tab === 'register') ? 'true' : 'false'; ?>">Register</button>
            </li>
        </ul>

        <div class="tab-content py-4" id="myTabContent">
            
            <div class="tab-pane fade <?php echo ($active_tab === 'login') ? 'show active' : ''; ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
                <?php if ($error_message && $active_tab === 'login'): ?>
                    <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form action="login_register.php" method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    <button type="submit" name="login_submit" class="btn btn-success w-100">Login</button>
                </form>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab === 'register') ? 'show active' : ''; ?>" id="register" role="tabpanel" aria-labelledby="register-tab">
                <?php if ($error_message && $active_tab === 'register'): ?>
                    <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form action="login_register.php" method="POST">
                    <div class="mb-3">
                        <label for="registerName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="registerName" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                    </div>
                    <button type="submit" name="register_submit" class="btn btn-success w-100">Register</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const authTitle = document.getElementById('auth-title');
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');

            if (loginTab && registerTab && authTitle) {
                loginTab.addEventListener('shown.bs.tab', () => {
                    authTitle.innerText = "Welcome Back!";
                });

                registerTab.addEventListener('shown.bs.tab', () => {
                    authTitle.innerText = "Create an Account!";
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>