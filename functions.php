<?php

function getUserProfile($userId, $mysqli) {
    $sql = "SELECT * FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $mysqli->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Error in user retrieval query: " . $mysqli->error);
    }

    return $result->fetch_assoc();
}

function getUserPosts($userId, $mysqli) {
    $userPostsQuery = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
    $userPostsStmt = $mysqli->prepare($userPostsQuery);

    if (!$userPostsStmt) {
        die("Error preparing statement: " . $mysqli->error);
    }

    $userPostsStmt->bind_param("i", $userId);
    $userPostsStmt->execute();
    $userPostsResult = $userPostsStmt->get_result();

    if (!$userPostsResult) {
        die("Error in user posts retrieval query: " . $mysqli->error);
    }

    return $userPostsResult->fetch_all(MYSQLI_ASSOC);
}

function fetchComments($mysqli, $postId) {
    $commentsQuery = "
        SELECT 
            comments.id AS comment_id,
            comments.user_id AS comment_user_id,
            comments.content AS comment_content,
            comments.created_at AS comment_created_at,
            user.name AS comment_user_name,
            user.profile_image AS comment_user_image
        FROM 
            comments
        JOIN 
            user ON comments.user_id = user.id
        WHERE 
            comments.post_id = ?
        ORDER BY 
            comments.created_at ASC";
    
    $stmt = $mysqli->prepare($commentsQuery);
    if (!$stmt) return [];
    
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $comments;
}

/**
 * Centralized function to search events with dynamic filters.
 */
function searchEvents($mysqli, $filters) {
    $query = "SELECT * FROM events WHERE 1=1";
    
    if (!empty($filters['search_term'])) {
        $searchTerm = $mysqli->real_escape_string($filters['search_term']);
        $query .= " AND (event_name LIKE '%$searchTerm%' OR event_description LIKE '%$searchTerm%')";
    }

    $map = [
        'event_type'      => ['field' => 'event_type',      'op' => '='],
        'topic'           => ['field' => 'topic',           'op' => '='],
        'country'         => ['field' => 'country',         'op' => '='],
        'start_date_from' => ['field' => 'start_date',      'op' => '>='],
        'start_date_to'   => ['field' => 'start_date',      'op' => '<='],
    ];

    foreach ($map as $key => $conf) {
        if (!empty($filters[$key])) {
            $val = $mysqli->real_escape_string($filters[$key]);
            $query .= " AND {$conf['field']} {$conf['op']} '$val'";
        }
    }

    return $mysqli->query($query);
}
?>
