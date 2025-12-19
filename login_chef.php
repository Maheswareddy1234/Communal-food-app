<?php
header("Content-Type: application/json");
require_once "db.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (
    empty($data["email"]) ||
    empty($data["password"])
) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

$email = $conn->real_escape_string($data["email"]);
$password = $data["password"];

// Fetch chef password
$sql = "SELECT password 
        FROM chefs 
        WHERE email = '$email' 
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

$chef = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $chef["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

// Login success (no chef details)
echo json_encode([
    "success" => true,
    "message" => "Home chef login successful"
]);
?>
