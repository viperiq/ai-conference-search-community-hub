<?php
session_start();

$mysqli = require __DIR__ . "/database.php";

// Set JSON response header
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"] ?? null;
    $post_id = $_POST["post_id"] ?? null;

    if (!$user_id || !$post_id) {
        echo json_encode(['success' => false, 'message' => 'Missing user or post ID']);
        exit();
    }

    if (isset($_POST["like"])) {
        handleLike($mysqli, $user_id, $post_id);
    } elseif (isset($_POST["comment"])) {
        handleComment($mysqli, $user_id, $post_id);
    }
}

function handleLike($mysqli, $user_id, $post_id) {
    // Check if the user has already liked the post
    $checkLikeQuery = "SELECT id FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $mysqli->prepare($checkLikeQuery);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // User hasn't liked the post yet, proceed to add a like
        $insertLikeQuery = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
        $stmt = $mysqli->prepare($insertLikeQuery);
        $stmt->bind_param("ii", $user_id, $post_id);

        if ($stmt->execute()) {
            // Update the post's likes count
            $updateLikesQuery = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
            $stmt = $mysqli->prepare($updateLikesQuery);
            $stmt->bind_param("i", $post_id);
            
            if ($stmt->execute()) {
                // Get updated likes count
                $countQuery = "SELECT likes FROM posts WHERE id = ?";
                $stmt = $mysqli->prepare($countQuery);
                $stmt->bind_param("i", $post_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                echo json_encode(['success' => true, 'liked' => true, 'likes_count' => $row['likes']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating likes count']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding like']);
        }
    } else {
        // User already liked the post, remove the like
        $deleteLikeQuery = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
        $stmt = $mysqli->prepare($deleteLikeQuery);
        $stmt->bind_param("ii", $user_id, $post_id);
        
        if ($stmt->execute()) {
            // Update the post's likes count (decrease)
            $updateLikesQuery = "UPDATE posts SET likes = likes - 1 WHERE id = ? AND likes > 0";
            $stmt = $mysqli->prepare($updateLikesQuery);
            $stmt->bind_param("i", $post_id);
            
            if ($stmt->execute()) {
                // Get updated likes count
                $countQuery = "SELECT likes FROM posts WHERE id = ?";
                $stmt = $mysqli->prepare($countQuery);
                $stmt->bind_param("i", $post_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                echo json_encode(['success' => true, 'liked' => false, 'likes_count' => $row['likes']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating likes count']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error removing like']);
        }
    }
}

function handleComment($mysqli, $user_id, $post_id) {
    // Handle commenting on a post
    $comment_content = $_POST["comment_content"] ?? '';
    
    if (empty($comment_content)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit();
    }
    
    $comment_content = htmlspecialchars($mysqli->real_escape_string($comment_content));
    $insertCommentQuery = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($insertCommentQuery);
    $stmt->bind_param("iis", $post_id, $user_id, $comment_content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding comment']);
    }
}
?>
