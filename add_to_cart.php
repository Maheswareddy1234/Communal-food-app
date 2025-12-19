<?php
header("Content-Type: application/json");
require_once "db.php";

/* Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON input"
    ]);
    exit;
}

/* Validate required fields */
if (
    !isset($data["customer_id"]) || trim((string)$data["customer_id"]) === "" ||
    !isset($data["dish_id"]) || trim((string)$data["dish_id"]) === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "customer_id and dish_id are required"
    ]);
    exit;
}

$customer_id = (int) $data["customer_id"];
$dish_id = (int) $data["dish_id"];
$quantity = isset($data["quantity"]) && (int)$data["quantity"] > 0
            ? (int)$data["quantity"]
            : 1;

/* Check if item already exists in cart */
$check = $conn->query(
    "SELECT id, quantity FROM cart 
     WHERE customer_id = '$customer_id' AND dish_id = '$dish_id'"
);

if ($check && $check->num_rows > 0) {

    // Update quantity
    $row = $check->fetch_assoc();
    $new_qty = $row["quantity"] + $quantity;

    $update = "UPDATE cart 
               SET quantity = '$new_qty'
               WHERE id = '{$row["id"]}'";

    if ($conn->query($update)) {
        echo json_encode([
            "success" => true,
            "message" => "Cart quantity updated"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to update cart"
        ]);
    }

} else {

    // Insert new cart item
    $insert = "INSERT INTO cart (customer_id, dish_id, quantity)
               VALUES ('$customer_id', '$dish_id', '$quantity')";

    if ($conn->query($insert)) {
        echo json_encode([
            "success" => true,
            "message" => "Item added to cart"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to add item to cart"
        ]);
    }
}
?>
