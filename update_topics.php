<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mysqli = require __DIR__ . "/database.php";

if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $topics = implode(',', $_POST['topics']);

    $updateTopicsQuery = "UPDATE user SET topics = ? WHERE id = ?";
    $stmt = $mysqli->prepare($updateTopicsQuery);

    if ($stmt) {
        $stmt->bind_param("si", $topics, $user_id);
        $stmt->execute();

        if ($stmt->errno) {
            die("Execute error: " . $stmt->error);
        }

        $stmt->close();
    } else {
        die("Error in prepare statement: " . $mysqli->error);
    }

    header("Location: profile.php");
    exit();
}

$mysqli->close();
header("Location: profile.php");
exit();
?>
