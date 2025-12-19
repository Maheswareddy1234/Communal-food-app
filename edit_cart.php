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
    !isset($data["cart_id"]) || trim((string)$data["cart_id"]) === "" ||
    !isset($data["quantity"]) || (int)$data["quantity"] < 1
) {
    echo json_encode([
        "success" => false,
        "message" => "cart_id and quantity (>=1) are required"
    ]);
    exit;
}

$cart_id = (int) $data["cart_id"];
$quantity = (int) $data["quantity"];

/* Update cart quantity */
$sql = "UPDATE cart
        SET quantity = '$quantity'
        WHERE id = '$cart_id'";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Cart updated successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Cart item not found"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update cart"
    ]);
}
?>
