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

/* Validate customer_id */
if (!isset($data["customer_id"]) || trim((string)$data["customer_id"]) === "") {
    echo json_encode([
        "success" => false,
        "message" => "customer_id is required"
    ]);
    exit;
}

$customer_id = (int) $data["customer_id"];

/* Fetch cart items with dish details */
$sql = "SELECT 
            c.id AS cart_id,
            c.quantity,
            d.id AS dish_id,
            d.dish_name,
            d.price,
            d.image,
            d.is_available
        FROM cart c
        JOIN dishes d ON c.dish_id = d.id
        WHERE c.customer_id = '$customer_id'";

$result = $conn->query($sql);

$items = [];
$total_amount = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row["item_total"] = $row["price"] * $row["quantity"];
        $total_amount += $row["item_total"];
        $items[] = $row;
    }
}

/* Response */
echo json_encode([
    "success" => true,
    "count" => count($items),
    "total_amount" => $total_amount,
    "data" => $items
]);
?>
