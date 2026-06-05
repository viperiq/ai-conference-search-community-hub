<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

// Retrieve user information
$user_id = $_SESSION['user_id'];
$user = getUserProfile($user_id, $mysqli);

// Retrieve posts for the logged-in user
$posts = getUserPosts($user_id, $mysqli);

// Function to check if user liked a post
function userLikedPost($mysqli, $user_id, $post_id) {
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
        .glass-modal { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
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
                        <p class="text-slate-500 text-sm mb-6"><?= $user['email']; ?></p>
                        
                        <button id="toggleTopics" class="w-full py-3 px-4 bg-slate-50 border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-100 transition mb-8">
                            Edit Profile Details
                        </button>

                        <div class="space-y-4 pt-6 border-t border-slate-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Interests</h3>
                            <div id="topicsContainer" class="flex flex-wrap gap-2">
                                <?php if (!empty($user['topics'])): ?>
                                    <?php foreach (explode(',', $user['topics']) as $topic): ?>
                                        <span class="px-4 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full border border-indigo-100 italic">#<?= htmlspecialchars(trim($topic)) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-slate-400 text-sm">No topics added yet</p>
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

                <?php if (!empty($posts)): ?>
                    <div class="space-y-6">
                        <?php foreach ($posts as $post): ?>
                            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition hover:shadow-lg">
                                <div class="p-6">
                                    <div class="flex items-center space-x-4 mb-6">
                                        <img src="uploads/<?= $user['profile_image'] ?: 'default-user.png' ?>" class="w-12 h-12 rounded-full object-cover">
                                        <div>
                                            <a href="profile-user.php?id=<?= $user['id'] ?>" class="font-bold text-slate-900 hover:text-indigo-600"><?= $user['name'] ?></a>
                                            <p class="text-xs text-slate-400"><?= $post['created_at'] ?></p>
                                        </div>
                                    </div>
                                    
                                    <h3 class="text-lg font-bold text-slate-900 mb-3"><?= htmlspecialchars($post["title"]) ?></h3>
                                    <p class="text-slate-600 text-sm leading-relaxed mb-6 whitespace-pre-wrap"><?= htmlspecialchars($post["content"]) ?></p>
                                    
                                    <?php if ($post["image_path"]): ?>
                                        <img src="<?= htmlspecialchars($post["image_path"]) ?>" class="w-full h-auto max-h-[500px] object-cover rounded-xl mb-6 border border-slate-100">
                                    <?php endif; ?>

                                    <div class="flex items-center space-x-6 pt-6 border-t border-slate-50">
                                        <button class="flex items-center text-slate-500 hover:text-rose-500 font-semibold text-sm transition">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                            <?= $post["likes"] ?? 0 ?> Likes
                                        </button>
                                        <button class="toggle-comment flex items-center text-slate-500 hover:text-indigo-600 font-semibold text-sm transition">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                            Comments
                                        </button>
                                    </div>
                                </div>

                                <!-- Hidden Comment Section (JS remains same) -->
                                <div class="comment-section bg-slate-50 p-6 border-t border-slate-100" style="display: none;">
                                    <form method="post" action="like_comment_handler.php" class="comment-form flex space-x-3 mb-6">
                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                        <input type="hidden" name="comment" value="1">
                                        <input name="comment_content" class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none" placeholder="Write a comment..." required>
                                        <button type="button" class="submit-comment bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-bold">Post</button>
                                    </form>
                                    <?php if (!empty($comments = fetchComments($mysqli, $post['id']))): ?>
                                        <div class="space-y-4">
                                            <?php foreach ($comments as $comment): ?>
                                                <div class="flex space-x-3">
                                                    <div class="flex-1 bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                                                        <a href="profile-user.php?id=<?= $comment['comment_user_id'] ?>" class="text-xs font-bold text-indigo-600 mb-1 block"><?= $comment['comment_user_name'] ?></a>
                                                        <p class="text-slate-600 text-sm"><?= nl2br(htmlspecialchars($comment["comment_content"])) ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl p-20 text-center border-2 border-dashed border-slate-200">
                        <p class="text-slate-400">No activity yet. Share your first post!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Glassmorphic Modal Wrapper for Edit Topics -->
    <div id="modalOverlay" class="fixed inset-0 z-50 glass-modal items-center justify-center p-6" style="display: none;">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 p-8">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Edit Interests</h2>
            <form id="topicsForm" action="update_topics.php" method="post" class="space-y-6">
                <div id="tagContainer" class="space-y-3">
                    <?php if (!empty($user['topics'])): ?>
                        <?php foreach (explode(',', $user['topics']) as $topic): ?>
                            <input type="text" name="topics[]" value="<?= htmlspecialchars(trim($topic)) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="text" name="topics[]" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                    <?php endif; ?>
                </div>
                <div class="flex space-x-3">
                    <button type="button" id="addTag" class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition">Add More</button>
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Save Changes</button>
                </div>
                <button type="button" id="closeModal" class="w-full text-slate-400 text-sm font-semibold hover:text-slate-600 transition">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Handle Modal Toggle
        const modal = document.getElementById('modalOverlay');
        document.getElementById('toggleTopics').onclick = () => modal.style.display = 'flex';
        document.getElementById('closeModal').onclick = () => modal.style.display = 'none';

        document.addEventListener('DOMContentLoaded', function () {
            // Add Tag Logic
            document.getElementById('addTag').onclick = () => {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'topics[]';
                input.className = 'w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500';
                document.getElementById('tagContainer').appendChild(input);
            };

            // Toggle Comments
            document.querySelectorAll(".toggle-comment").forEach(btn => {
                btn.onclick = () => {
                    const section = btn.closest('article').querySelector('.comment-section');
                    section.style.display = section.style.display === 'none' ? 'block' : 'none';
                };
            });
        });
    </script>
    <!-- Additional scripts -->
    <script>
        // Handle dynamic addition and removal of tag input fields
        // Toggle Comments Visibility
        // AJAX Like Handler - All in one DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('toggleTopics');
            const topicsContainer = document.getElementById('topicsContainer');
            const topicsForm = document.getElementById('topicsForm');
            const tagContainer = document.getElementById('tagContainer');
            const addTagButton = document.getElementById('addTag');

            if (toggleButton) {
                toggleButton.addEventListener('click', function () {
                    topicsContainer.style.display = 'none';
                    topicsForm.style.display = 'block';
                });
            }

            if (addTagButton) {
                addTagButton.addEventListener('click', function () {
                    const newTagInput = document.createElement('input');
                    newTagInput.type = 'text';
                    newTagInput.name = 'topics[]';
                    tagContainer.appendChild(newTagInput);
                });
            }

            // Attach click event to all toggle-comment buttons
            var toggleCommentButtons = document.querySelectorAll(".toggle-comment");
            toggleCommentButtons.forEach(function (button) {
                button.addEventListener("click", function () {
                    var post = this.closest(".post");
                    var commentSection = post.querySelector(".comment-section");
                    commentSection.style.display = (commentSection.style.display === "none" || commentSection.style.display === "") ? "block" : "none";
                });
            });

            // AJAX Like Handler
            console.log("Initializing like handler...");
            var likeButtons = document.querySelectorAll("button.like-btn");
            console.log("Found " + likeButtons.length + " like buttons");
            
            likeButtons.forEach(function(btn) {
                console.log("Attaching like handler to button");
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    console.log("Like button clicked");
                    
                    const form = this.closest(".like-form");
                    const postId = form.querySelector('input[name="post_id"]').value;
                    
                    console.log("Sending like request for post: " + postId);
                    
                    const formData = new FormData(form);
                    formData.set('like', '1');
                    
                    fetch("like_comment_handler.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => {
                        console.log("Response status:", response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log("Response data:", data);
                        if (data.success) {
                            if (data.liked) {
                                this.textContent = '❤️ Liked (' + data.likes_count + ')';
                                this.setAttribute('data-liked', 'true');
                            } else {
                                this.textContent = '❤️ Like (' + data.likes_count + ')';
                                this.setAttribute('data-liked', 'false');
                            }
                        } else {
                            console.error("Request failed:", data.message);
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert("Error: " + error);
                    });
                });
            });

            // AJAX Comment Handler
            console.log("Initializing comment handler...");
            var commentForms = document.querySelectorAll(".comment-form");
            console.log("Found " + commentForms.length + " comment forms");
            
            commentForms.forEach(function(form) {
                var submitBtn = form.querySelector(".submit-comment");
                if (submitBtn) {
                    submitBtn.addEventListener("click", function (e) {
                        e.preventDefault();
                        console.log("Comment submit clicked");
                        
                        const commentInput = form.querySelector('input[name="comment_content"]');
                        const commentText = commentInput.value.trim();
                        
                        if (!commentText) {
                            alert("Please write a comment");
                            return;
                        }
                        
                        const formData = new FormData(form);
                        
                        fetch("like_comment_handler.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Comment response:", data);
                            if (data.success) {
                                commentInput.value = '';
                                alert("Comment added successfully!");
                                // Reload the page to show new comment
                                location.reload();
                            } else {
                                console.error("Comment failed:", data.message);
                                alert("Error: " + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            alert("Error: " + error);
                        });
                    });
                }
            });
        });
    </script>

</body>
</html>
