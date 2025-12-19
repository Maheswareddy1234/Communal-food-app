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

/* Validate cart_id */
if (!isset($data["cart_id"]) || trim((string)$data["cart_id"]) === "") {
    echo json_encode([
        "success" => false,
        "message" => "cart_id is required"
    ]);
    exit;
}

$cart_id = (int) $data["cart_id"];

/* Delete cart item */
$sql = "DELETE FROM cart WHERE id = '$cart_id'";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Cart item deleted successfully"
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
        "message" => "Failed to delete cart item"
    ]);
}
?>
