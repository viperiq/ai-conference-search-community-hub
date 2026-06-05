<?php

if (empty($_POST["name"]) || empty($_POST["email"]) || empty($_POST["password"])) {
    die("All fields are required");
}

if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if (!preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

$pass_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$mysqli = require __DIR__ . "/database.php";

// Move the upload handling part here
$uploadDir = 'uploads/';
$uploadedFile = $uploadDir . basename($_FILES['profile_image']['name']);

if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadedFile)) {
    // File successfully uploaded, store the filename in the database
    $profile_image = basename($_FILES['profile_image']['name']);
} else {
    die("Image upload failed.");
}

$sql = "INSERT INTO user (name, email, pass_hash, profile_image)
        VALUES (?, ?, ?, ?)";
        
$stmt = $mysqli->stmt_init();

if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("ssss",
    $_POST["name"],
    $_POST["email"],
    $pass_hash,
    $profile_image
);

if ($stmt->execute()) {
    header("Location: login.php");
    exit;
} else {
    if ($mysqli->errno === 1062) {
        die("Email already taken");
    } else {
        die("Error: " . $mysqli->error);
    }
}
