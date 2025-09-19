<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "luxliving";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add_to_cart' || $action === 'add_to_wishlist') {
        $product_id = intval($_POST['product_id']);
        if ($action === 'add_to_cart') {
            $sql = "INSERT INTO cart_items (product_id) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Product added to cart!']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart.']);
            }
            $stmt->close();
        } elseif ($action === 'add_to_wishlist') {
            $check_sql = "SELECT wishlist_item_id FROM wishlist_items WHERE product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            if ($check_stmt->num_rows > 0) {
                echo json_encode(['status' => 'info', 'message' => 'Product is already in your wishlist.']);
            } else {
                $sql = "INSERT INTO wishlist_items (product_id) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $product_id);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Product added to wishlist!']);
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add product to wishlist.']);
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    } elseif ($action === 'add_review') {
        $plant_id = intval($_POST['plant_id']);
        $reviewer_name = $_POST['reviewer_name'];
        $rating = intval($_POST['rating']);
        $comment = $_POST['comment'];

        if ($plant_id && $reviewer_name && $rating && $comment) {
            $sql = "INSERT INTO reviews (plant_id, reviewer_name, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isis", $plant_id, $reviewer_name, $rating, $comment);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Review submitted successfully!', 'new_review' => ['reviewer_name' => $reviewer_name, 'rating' => $rating, 'comment' => $comment, 'created_at' => date('Y-m-d H:i:s')]]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to submit review.']);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided for review.']);
        }
    } elseif ($action === 'remove_item') {
        $product_id = intval($_POST['product_id']);
        $sql = "DELETE FROM cart_items WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove item.']);
        }
        $stmt->close();

    } elseif ($action === 'update_quantity') {
        // You also need to add logic to handle the 'update_quantity' action
        // which is being called in your script.js.
        // The following is an example implementation:
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);

        $sql = "UPDATE cart_items SET quantity = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Quantity updated.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity.']);
        }
        $stmt->close();

    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
}
$conn->close();
?>