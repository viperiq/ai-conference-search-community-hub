    <?php
    session_start();

    $mysqli = require __DIR__ . "/database.php";

// Handle likes and comments
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $event_id = $_POST["event_id"];

    if (isset($_POST["like"])) {
        handleLike($mysqli, $user_id, $event_id);
    } elseif (isset($_POST["comment"])) {
        handleComment($mysqli, $user_id, $event_id);
    }
}


    function handleLike($mysqli, $user_id, $event_id) {
        // Check if the user has already liked the event
        $checkLikeQuery = "SELECT * FROM event_likes WHERE user_id = ? AND event_id = ?";
        $stmt = $mysqli->prepare($checkLikeQuery);
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // User hasn't liked the event yet, proceed to add a like
            $insertLikeQuery = "INSERT INTO event_likes (user_id, event_id) VALUES (?, ?)";
            $stmt = $mysqli->prepare($insertLikeQuery);
            $stmt->bind_param("ii", $user_id, $event_id);

            if ($stmt->execute()) {
                // Update the event's likes count
                updateLikesCount($mysqli, $event_id);
            } else {
                echo "Error adding like: " . $mysqli->error;
            }
        } else {
            // User already liked the event
            echo "You already liked this event.";
        }
    }

    function updateLikesCount($mysqli, $event_id) {
        $updateLikesQuery = "UPDATE events SET likes = likes + 1 WHERE id = ?";
        $stmt = $mysqli->prepare($updateLikesQuery);
        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {
            // Redirect back to the events page after successful like
            header("Location: events_search.php");
            exit();
        } else {
            echo "Error updating likes: " . $mysqli->error;
        }
    }

    function handleComment($mysqli, $user_id, $event_id) {
        // Handle commenting on an event
        $comment_text = htmlspecialchars($mysqli->real_escape_string($_POST["comment_text"]));
        $insertCommentQuery = "INSERT INTO event_comments (event_id, user_id, comment_text) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($insertCommentQuery);
        $stmt->bind_param("iis", $event_id, $user_id, $comment_text);

        if ($stmt->execute()) {
            // Redirect back to the events page after successful comment
            header("Location: events_search.php");
            exit();
        } else {
            echo "Error adding comment: " . $mysqli->error;
        }
    }
    ?>
