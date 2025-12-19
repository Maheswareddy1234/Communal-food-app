<?php
header("Content-Type: application/json");
require_once "db.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    empty($data["name"]) ||
    empty($data["email"]) ||
    empty($data["phone"]) ||
    empty($data["password"]) ||
    empty($data["confirm_password"])
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

// Check password match
if ($data["password"] !== $data["confirm_password"]) {
    echo json_encode([
        "success" => false,
        "message" => "Password and confirm password do not match"
    ]);
    exit;
}

$name  = $conn->real_escape_string($data["name"]);
$email = $conn->real_escape_string($data["email"]);
$phone = $conn->real_escape_string($data["phone"]);
$password = password_hash($data["password"], PASSWORD_DEFAULT);

// Check if email already exists
$check = $conn->query("SELECT id FROM chefs WHERE email='$email'");
if ($check->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email already registered"
    ]);
    exit;
}

// Insert chef
$sql = "INSERT INTO chefs (name, email, phone, password)
        VALUES ('$name', '$email', '$phone', '$password')";

if ($conn->query($sql)) {
    echo json_encode([
        "success" => true,
        "message" => "Home chef registered successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Registration failed"
    ]);
}
?>
