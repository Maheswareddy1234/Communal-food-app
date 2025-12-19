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

/* Validate dish_id */
if (!isset($data["dish_id"]) || trim((string)$data["dish_id"]) === "") {
    echo json_encode([
        "success" => false,
        "message" => "dish_id is required"
    ]);
    exit;
}

$dish_id = (int) $data["dish_id"];

/* Delete query */
$sql = "DELETE FROM dishes WHERE id = '$dish_id'";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Dish deleted successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Dish not found"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete dish"
    ]);
}
?>
