<?php
session_start();
$userId = isset($_GET['id']) ? $_GET['id'] : null;

if ($userId === null) {
    die("User ID not provided.");
}

$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

// Get current logged-in user ID
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Retrieve user information
$user = getUserProfile($userId, $mysqli);

// Retrieve posts for the user
$userPosts = getUserPosts($userId, $mysqli);

// Function to check if user liked a post
function userLikedPost($mysqli, $user_id, $post_id) {
    if (!$user_id) return false; // Not logged in
    $query = "SELECT id FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['name']) ?> | Profile</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <?php require_once __DIR__ . "/navbar.php"; ?>

    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Left: Premium Profile Sidebar -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative">
                    <div class="h-32 bg-gradient-to-br from-indigo-600 to-indigo-900"></div>
                    <div class="px-8 pb-10">
                        <div class="relative -mt-16 mb-6 inline-block">
                            <div class="p-1.5 bg-white rounded-full shadow-md">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="uploads/<?= $user['profile_image']; ?>" class="w-32 h-32 rounded-full object-cover border-4 border-slate-50">
                                <?php else: ?>
                                    <img src="images/user.png" class="w-32 h-32 rounded-full border-4 border-slate-50">
                                <?php endif; ?>
                            </div>
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 mb-1"><?= $user['name']; ?></h1>
                        <p class="text-slate-500 text-sm mb-8"><?= $user['email']; ?></p>

                        <div class="space-y-4 pt-6 border-t border-slate-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Interests</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($user['topics'])): ?>
                                    <?php foreach (explode(',', $user['topics']) as $topic): ?>
                                        <span class="px-4 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full border border-indigo-100 italic">#<?= htmlspecialchars(trim($topic)) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-slate-400 text-sm italic">No topics added yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Activity Feed -->
            <div class="lg:col-span-8 space-y-8">
                <h2 class="text-xl font-bold text-slate-800 flex items-center">
                    <span class="w-2 h-8 bg-emerald-500 rounded-full mr-4"></span>
                    Activity Feed
                </h2>

                <?php if (!empty($userPosts)): ?>
                    <div class="space-y-6">
                        <?php foreach ($userPosts as $post): ?>
                            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition hover:shadow-lg">
                                <div class="p-8">
                                    <div class="flex items-center space-x-4 mb-6">
                                        <img src="uploads/<?= $user['profile_image'] ?: 'user.png' ?>" class="w-12 h-12 rounded-full object-cover">
                                        <div>
                                            <p class="font-bold text-slate-900"><?= htmlspecialchars($user['name']) ?></p>
                                            <p class="text-xs text-slate-400"><?= $post['created_at'] ?></p>
                                        </div>
                                    </div>
                                    
                                    <h3 class="text-lg font-bold text-slate-900 mb-3"><?= htmlspecialchars($post["title"]) ?></h3>
                                    <p class="text-slate-600 text-sm leading-relaxed mb-6 whitespace-pre-wrap"><?= htmlspecialchars($post["content"]) ?></p>
                                    
                                    <?php if ($post["image_path"]): ?>
                                        <img src="<?= htmlspecialchars($post["image_path"]) ?>" class="w-full h-auto max-h-[500px] object-cover rounded-xl mb-6 border border-slate-100">
                                    <?php endif; ?>

                                    <div class="flex items-center space-x-6 pt-6 border-t border-slate-50">
                                        <form method="post" action="like_comment_handler.php" class="like-form flex items-center">
                                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
                                            <input type="hidden" name="like" value="1">
                                            <?php 
                                                $is_liked = userLikedPost($mysqli, $current_user_id, $post['id']);
                                                $btn_text = $is_liked ? "❤️ Liked" : "❤️ Like";
                                            ?>
                                            <button type="button" class="like-btn flex items-center text-slate-500 hover:text-rose-500 font-semibold text-sm transition" data-post-id="<?= htmlspecialchars($post['id']) ?>" data-liked="<?= $is_liked ? 'true' : 'false' ?>">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                                <?= $btn_text ?> (<?= $post["likes"] ?? 0 ?>)
                                            </button>
                                        </form>
                                        <button class="toggle-comment flex items-center text-slate-500 hover:text-indigo-600 font-semibold text-sm transition">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                            Comments
                                        </button>
                                </div>

                                <!-- Hidden Comment Section -->
                                <div class="comment-section bg-slate-50 p-8 border-t border-slate-100" style="display: none;">
                                    <form method="post" action="like_comment_handler.php" class="comment-form flex space-x-3 mb-8">
                                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
                                        <input type="hidden" name="comment" value="1">
                                        <input name="comment_content" class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" placeholder="Write a comment..." required>
                                        <button type="button" class="submit-comment bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-bold">Post</button>
                                    </form>
                                    <?php if (!empty($comments = fetchComments($mysqli, $post['id']))): ?>
                                        <div class="space-y-4">
                                            <?php foreach ($comments as $comment): ?>
                                                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                                    <a href="profile-user.php?id=<?= htmlspecialchars($comment['comment_user_id']) ?>" class="text-xs font-bold text-indigo-600 mb-1 block"><?= htmlspecialchars($comment['comment_user_name']) ?></a>
                                                    <p class="text-slate-600 text-sm"><?= nl2br(htmlspecialchars($comment["comment_content"])) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-3xl p-20 text-center border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-medium">No activity yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle Comments logic
            document.querySelectorAll(".toggle-comment").forEach(btn => {
                btn.onclick = () => {
                    const section = btn.closest('article').querySelector('.comment-section');
                    if (section) {
                        section.style.display = section.style.display === 'none' || section.style.display === '' ? 'block' : 'none';
                    }
                };
            });

            // AJAX Like Handler
            document.querySelectorAll("button.like-btn").forEach(function(btn) {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    const form = this.closest(".like-form");
                    const formData = new FormData(form);
                    formData.set('like', '1');
                    
                    fetch("like_comment_handler.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>` + 
                                             (data.liked ? '❤️ Liked' : '❤️ Like') + ' (' + data.likes_count + ')';
                        }
                    });
                });
            });

            // AJAX Comment Handler
            document.querySelectorAll(".comment-form").forEach(function(form) {
                const submitBtn = form.querySelector(".submit-comment");
                if (submitBtn) {
                    submitBtn.addEventListener("click", function (e) {
                        e.preventDefault();
                        const commentInput = form.querySelector('input[name="comment_content"]');
                        if (!commentInput.value.trim()) return;
                        
                        const formData = new FormData(form);
                        fetch("like_comment_handler.php", { method: "POST", body: formData })
                        .then(response => response.json())
                        .then(data => { if (data.success) location.reload(); });
                    });
                }
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
