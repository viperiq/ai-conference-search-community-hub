<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$user = getUserProfile($user_id, $mysqli);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["post_title"])) {
    $post_title = htmlspecialchars($mysqli->real_escape_string($_POST["post_title"]));
    $post_content = htmlspecialchars($mysqli->real_escape_string($_POST["post_content"]));
    
    $imagePath = null;
    if (isset($_FILES["post_image"]) && $_FILES["post_image"]["error"] === UPLOAD_ERR_OK) {
        $uploadDir = 'upload/';
        $imagePath = $uploadDir . uniqid() . '_' . basename($_FILES["post_image"]["name"]);
        move_uploaded_file($_FILES["post_image"]["tmp_name"], $imagePath);
    }

    $stmt = $mysqli->prepare("INSERT INTO posts (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $post_title, $post_content, $imagePath);
    $stmt->execute();
    header("Location: post.php");
    exit();
}

$postsQuery = "SELECT posts.*, user.name AS user_name, user.profile_image AS user_profile_image, 
               (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count
               FROM posts 
               JOIN user ON posts.user_id = user.id 
               ORDER BY posts.created_at DESC";
$postsResult = $mysqli->query($postsQuery);
$posts = $postsResult ? $postsResult->fetch_all(MYSQLI_ASSOC) : [];

foreach ($posts as &$post) {
    // Fix: Map flat join results to the nested 'user' array expected by the template
    $post['user'] = [
        'id' => $post['user_id'],
        'name' => $post['user_name'],
        'profile_image' => $post['user_profile_image']
    ];
    $post['comments'] = fetchComments($mysqli, $post['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Feed | ConfHub</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <?php require_once __DIR__ . "/navbar.php"; ?>

    <main class="max-w-2xl mx-auto px-6 py-12">
        
        <!-- Modern Post Composer -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-10">
            <div class="flex items-start space-x-4">
                <img src="uploads/<?= $user['profile_image'] ?: 'default-user.png' ?>" class="w-12 h-12 rounded-full object-cover">
                <form method="post" action="post.php" enctype="multipart/form-data" class="flex-1 space-y-4" x-data="{ imagePreview: null }">
                    <input type="text" name="post_title" placeholder="Give your post a title..." class="w-full text-lg font-bold outline-none border-b border-transparent focus:border-indigo-100 pb-2 placeholder:text-slate-300" required>
                    <textarea name="post_content" placeholder="Share an insight or update..." class="w-full text-slate-600 outline-none resize-none min-h-[100px] leading-relaxed" required></textarea>
                    
                    <div x-show="imagePreview" class="relative rounded-xl overflow-hidden border border-slate-100">
                        <img :src="imagePreview" class="w-full h-auto max-h-96 object-cover">
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                        <label class="flex items-center text-slate-400 hover:text-indigo-600 cursor-pointer transition">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs font-bold uppercase tracking-wider">Add Photo</span>
                            <input type="file" name="post_image" accept="image/*" class="hidden" @change="imagePreview = URL.createObjectURL($event.target.files[0])">
                        </label>
                        <button type="submit" class="bg-indigo-600 text-white px-8 py-2.5 rounded-full font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition transform hover:-translate-y-0.5">Post Content</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Community Feed -->
        <div class="space-y-8">
            <?php foreach ($posts as $p): ?>
                <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ commentsOpen: false }">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <?php $avatar = !empty($p['user']['profile_image']) ? 'uploads/' . $p['user']['profile_image'] : 'images/default-user.png'; ?>
                                <img src="<?= $avatar ?>" class="w-11 h-11 rounded-full object-cover">
                                <div>
                                    <a href="profile-user.php?id=<?= $p['user']['id'] ?>" class="font-bold text-slate-900 block hover:text-indigo-600 transition"><?= htmlspecialchars($p['user']['name']) ?></a>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-300"><?= $p['created_at'] ?></p>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-slate-900 mb-3 leading-tight"><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="text-slate-600 leading-relaxed mb-6"><?= nl2br(htmlspecialchars($p['content'])) ?></p>
                        
                        <?php if ($p['image_path']): ?>
                            <div class="rounded-2xl overflow-hidden border border-slate-50 mb-6">
                                <img src="<?= htmlspecialchars($p['image_path']) ?>" class="w-full h-auto max-h-[600px] object-cover">
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center space-x-6 pt-4 border-t border-slate-50">
                            <button class="flex items-center text-slate-400 hover:text-rose-500 transition group">
                                <svg class="w-5 h-5 mr-1.5 transition group-hover:fill-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span class="text-sm font-bold"><?= $p['likes'] ?></span>
                            </button>
                            <button @click="commentsOpen = !commentsOpen" class="toggle-comment flex items-center text-slate-400 hover:text-indigo-600 transition">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                <span class="text-sm font-bold"><?= $p['comment_count'] ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Premium Hidden Comments -->
                    <div x-show="commentsOpen" class="comment-section bg-slate-50 border-t border-slate-100 p-6">
                        <div class="space-y-4">
                            <?php foreach ($p['comments'] as $c): ?>
                                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                                    <span class="text-xs font-bold text-indigo-600 mb-1 block"><?= htmlspecialchars($c['comment_user_name']) ?></span>
                                    <p class="text-sm text-slate-600"><?= htmlspecialchars($c['comment_content']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>