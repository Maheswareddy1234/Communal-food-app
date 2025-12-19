<?php
header("Content-Type: application/json");
require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

if (!isset($data["customer_id"]) || trim($data["customer_id"]) === "") {
    echo json_encode(["success" => false, "message" => "customer_id is required"]);
    exit;
}

$customer_id = (int)$data["customer_id"];

/* Fetch cart */
$cart_sql = "SELECT c.dish_id, c.quantity, d.price, d.chef_id, d.is_available
             FROM cart c
             JOIN dishes d ON c.dish_id = d.id
             WHERE c.customer_id = '$customer_id'";

$cart = $conn->query($cart_sql);

if (!$cart || $cart->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Cart is empty"]);
    exit;
}

$total = 0;
$chef_id = null;
$items = [];

while ($row = $cart->fetch_assoc()) {

    if ((int)$row["is_available"] === 0) {
        echo json_encode(["success" => false, "message" => "Dish not available"]);
        exit;
    }

    if ($chef_id === null) {
        $chef_id = (int)$row["chef_id"];
    } elseif ($chef_id !== (int)$row["chef_id"]) {
        echo json_encode(["success" => false, "message" => "Multiple chefs not allowed"]);
        exit;
    }

    $total += $row["price"] * $row["quantity"];
    $items[] = $row;
}

/* Start transaction */
$conn->begin_transaction();

try {

    /* Insert order */
    $order_sql = "INSERT INTO orders (customer_id, chef_id, total_amount)
                  VALUES ('$customer_id', '$chef_id', '$total')";

    if (!$conn->query($order_sql)) {
        throw new Exception($conn->error);
    }

    $order_id = $conn->insert_id;

    /* Insert order items */
    foreach ($items as $item) {
        $item_sql = "INSERT INTO order_items (order_id, dish_id, quantity, price)
                     VALUES (
                        '$order_id',
                        '{$item["dish_id"]}',
                        '{$item["quantity"]}',
                        '{$item["price"]}'
                     )";

        if (!$conn->query($item_sql)) {
            throw new Exception($conn->error);
        }
    }

    /* Clear cart */
    if (!$conn->query("DELETE FROM cart WHERE customer_id = '$customer_id'")) {
        throw new Exception($conn->error);
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Order placed successfully",
        "order_id" => $order_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "success" => false,
        "message" => "Failed to place order",
        "error" => $e->getMessage()
    ]);
}
?>
