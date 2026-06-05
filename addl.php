<?php
session_start();

// Include the database connection file
$mysqli = require __DIR__ . "/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if (isset($_SESSION['user'])) {
        // Get user ID from the session
        $userId = $_SESSION['user']['id'];

        // Get event ID from the form
        $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

        // Check if the user has already liked the event
        $checkLikeQuery = "SELECT * FROM event_likes WHERE event_id = $eventId AND user_id = $userId";
        $checkLikeResult = $mysqli->query($checkLikeQuery);

        if ($checkLikeResult->num_rows == 0) {
            // If the user hasn't liked the event, insert the like
            $insertLikeQuery = "INSERT INTO event_likes (event_id, user_id) VALUES ($eventId, $userId)";
            if ($mysqli->query($insertLikeQuery)) {
                // Like inserted successfully
                header("Location: events_search.php"); // Redirect to the search results page
                exit();
            } else {
                // Error inserting like
                die("Error inserting like: " . $mysqli->error);
            }
        } else {
            // User has already liked the event
            header("Location: events_search.php"); // Redirect to the search results page
            exit();
        }
    } else {
        // User not logged in
        header("Location: login.php"); // Redirect to the login page
        exit();
    }
} else {
    // Invalid request method
    header("Location: events_search.php"); // Redirect to the search results page
    exit();
}
?>
