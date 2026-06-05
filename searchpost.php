<?php
session_start();
$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

$user = null;
if (isset($_SESSION["user_id"])) {
    $user = getUserProfile((int)$_SESSION["user_id"], $mysqli);
}
// Main code
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["q"])) {
    $searchQuery = '%' . $mysqli->real_escape_string($_GET["q"]) . '%';

    $searchPostsQuery = "
        SELECT 
            posts.id AS post_id,
            posts.title AS post_title,
            posts.content AS post_content,
            posts.created_at AS post_created_at,
            posts.image_path AS post_image_path,
            posts.likes AS post_likes,
            user.name AS user_name,
            user.profile_image AS user_profile_image,
            user.id AS user_id,
            COUNT(comments.id) AS comment_count
        FROM 
            posts
        JOIN 
            user ON posts.user_id = user.id
        LEFT JOIN 
            comments ON posts.id = comments.post_id
        WHERE 
            posts.title LIKE ? OR posts.content LIKE ?
        GROUP BY 
            posts.id
        ORDER BY 
            posts.created_at DESC";
    
    $stmt = $mysqli->prepare($searchPostsQuery);
    
    if (!$stmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    
    $stmt->bind_param("ss", $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $searchResults = [];
        while ($row = $result->fetch_assoc()) {
            // Fetch comments for the current post
            $row['comments'] = fetchComments($mysqli, $row['post_id']);
            
            // Add the post and its comments to the search results
            $searchResults[] = $row;
        }
    } else {
        die("Error fetching search results: " . $mysqli->error);
    }
    
    $stmt->close();
}

$mysqli->close();

// Now, the $searchResults array contains posts and their comments, as well as user information

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Community | ConfHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="max-w-2xl mx-auto px-6 py-12">
        <!-- Premium Search Bar -->
        <form method="get" action="searchpost.php" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-2 flex mb-12">
            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search community posts..." class="flex-1 bg-transparent px-6 outline-none text-slate-700 font-medium">
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-indigo-700 transition">Search</button>
        </form>

        <!-- Search Results Feed -->
        <?php if (isset($searchResults) && !empty($searchResults)): ?>
            <div class="space-y-8">
                <?php foreach ($searchResults as $post): ?>
                    <article class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden transition hover:shadow-md">
                        <div class="p-8">
                            <div class="flex items-center space-x-4 mb-6">
                                <img src="uploads/<?= $post['user_profile_image'] ?: 'default-user.png' ?>" class="w-12 h-12 rounded-full object-cover">
                                <div>
                                    <a href="profile-user.php?id=<?= $post['user_id'] ?>" class="font-bold text-slate-900 hover:text-indigo-600 block"><?= htmlspecialchars($post['user_name']) ?></a>
                                    <p class="text-xs text-slate-400"><?= $post['post_created_at'] ?></p>
                                </div>
                            </div>

                            <h3 class="text-xl font-extrabold text-slate-900 mb-3"><?= htmlspecialchars($post["post_title"]) ?></h3>
                            <p class="text-slate-600 leading-relaxed mb-6"><?= nl2br(htmlspecialchars($post["post_content"])) ?></p>

                            <?php if ($post["post_image_path"]): ?>
                                <img src="<?= "/final - v2/" . htmlspecialchars($post["post_image_path"]) ?>" class="w-full h-auto rounded-2xl border border-slate-100 mb-6">
                            <?php endif; ?>

                            <div class="flex items-center space-x-6 pt-6 border-t border-slate-50">
                                <button class="flex items-center text-slate-500 hover:text-rose-500 font-semibold text-sm transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    <?= $post["post_likes"] ?? 0 ?> Likes
                                </button>
                                <button class="toggle-comment flex items-center text-slate-500 hover:text-indigo-600 font-semibold text-sm transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <?= $post["comment_count"] ?> Comments
                                </button>
                            </div>
                        </div>

                        <!-- Hidden Comment Section -->
                        <div class="comment-section bg-slate-50 p-8 border-t border-slate-100 hidden">
                            <div class="space-y-4">
                                <?php foreach ($post['comments'] as $c): ?>
                                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                        <a href="profile-user.php?id=<?= $c['comment_user_id'] ?>" class="text-xs font-bold text-indigo-600 mb-1 block"><?= $c['comment_user_name'] ?></a>
                                        <p class="text-slate-600 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($c['comment_content'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-20 text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-medium">No community matches found for your search.</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Attach click event to all toggle-comment buttons
            var toggleCommentButtons = document.querySelectorAll(".toggle-comment");
            toggleCommentButtons.forEach(function (button) {
                button.addEventListener("click", function () {
                    var post = this.closest("article");
                    var commentSection = post.querySelector(".comment-section");
                    commentSection.classList.toggle('hidden');
                });
            });
        });
    </script>
<script>
window.embeddedChatbotConfig = {
chatbotId: "q7wx_BmpR61al2C_yC8WY",
domain: "www.chatbase.co"
}
</script>
<script
src="https://www.chatbase.co/embed.min.js"
chatbotId="q7wx_BmpR61al2C_yC8WY"
domain="www.chatbase.co"
defer>
</script>
</body>

</html>
