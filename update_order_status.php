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
    !isset($data["order_id"]) || trim((string)$data["order_id"]) === "" ||
    !isset($data["status"]) || !in_array($data["status"], ["accepted", "rejected"], true)
) {
    echo json_encode([
        "success" => false,
        "message" => "order_id and status (accepted/rejected) are required"
    ]);
    exit;
}

$order_id = (int) $data["order_id"];
$status = $conn->real_escape_string($data["status"]);

/* Update order status */
$sql = "UPDATE orders 
        SET status = '$status'
        WHERE id = '$order_id'";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Order $status successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update order status"
    ]);
}
?>
