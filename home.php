<?php
session_start();
// Error reporting for debugging (Remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. Retrieve user information (Done ONLY ONCE now)
$user_id = $_SESSION["user_id"];
$user = getUserProfile($user_id, $mysqli);

if (!$user) {
    // Handle case where user ID exists in session but not DB
    session_destroy();
    header("Location: login.php");
    exit();
}

// 2. Retrieve posts for the logged-in user
$postsQuery = "SELECT posts.*, user.name AS user_name FROM posts
               JOIN user ON posts.user_id = user.id
               ORDER BY posts.created_at DESC";
$postsResult = $mysqli->query($postsQuery);
$posts = $postsResult ? $postsResult->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Event Portal</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
    <style>body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <?php require_once __DIR__ . "/navbar.php"; ?>

    <header class="bg-slate-100 py-20 px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 mb-4">Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
        <p class="text-slate-500 mb-10 max-w-xl mx-auto">Discover events, connect with peers, and share your knowledge.</p>
        <form method="get" action="combined_search.php" class="max-w-xl mx-auto bg-white rounded-full shadow-lg border border-slate-200 p-2 flex">
            <input type="text" name="search_term" required placeholder="Search events, topics, or people..." class="flex-1 bg-transparent px-6 text-lg outline-none">
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-full font-bold hover:bg-indigo-700 transition">Search</button>
        </form>
    </header>

    <div class="max-w-7xl mx-auto py-16 px-6">
        <div class="swiper rounded-2xl shadow-lg border border-slate-200">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="images/1.png" alt="Event 1"></div>
                <div class="swiper-slide"><img src="images/photo.png" alt="Event 2"></div>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>

    <section class="max-w-7xl mx-auto py-16 px-6">
        <h2 class="text-3xl font-bold text-slate-900 mb-10 text-center">Events You May Like</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $userTopics = explode(',', $user['topics'] ?? ''); 
            $escapedTopics = array_map(function($t) use ($mysqli) {
                return "'" . $mysqli->real_escape_string(trim($t)) . "'";
            }, $userTopics);
            
            if(!empty($escapedTopics)) {
                $topicString = implode(",", $escapedTopics);
                $similarTopicsQuery = "SELECT * FROM events WHERE topic IN ($topicString) ORDER BY start_date DESC LIMIT 6";
                $similarTopicsResult = $mysqli->query($similarTopicsQuery);

                if ($similarTopicsResult && $similarTopicsResult->num_rows > 0) {
                    while ($row = $similarTopicsResult->fetch_assoc()) {
                        ?>
                        <a href='event_details.php?event_id=<?= $row['id'] ?>' class='block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden group transition hover:shadow-xl hover:-translate-y-1'>
                            <div class='h-48 bg-cover bg-center' style='background-image: url("<?= htmlspecialchars($row['image_path']) ?>")'></div>
                            <div class='p-6'>
                                <span class='inline-block px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full uppercase tracking-wider mb-3'><?= htmlspecialchars($row['event_type']) ?></span>
                                <h3 class='font-bold text-lg text-slate-900 mb-2 group-hover:text-indigo-600 transition'><?= htmlspecialchars($row['event_name']) ?></h3>
                                <p class='text-sm text-slate-500 mb-4'>📅 Starts: <?= htmlspecialchars($row['start_date']) ?></p>
                                <p class='text-sm text-slate-600 line-clamp-2'><?= htmlspecialchars($row['event_description']) ?></p>
                            </div>
                        </a>
                        <?php
                    }
                } else {
                    echo "<p class='lg:col-span-3 text-center text-slate-500'>No matching events found for your interests.</p>";
                }
            } else {
                 echo "<p class='lg:col-span-3 text-center text-slate-500'>Add topics to your profile to see personalized event recommendations.</p>";
            }
            ?>
        </div>
    </section>

    <section class="max-w-5xl mx-auto py-20 px-6">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <iframe src="https://viperiq.github.io/portfolio/index.html" 
                    class="w-full h-[600px] border-0" 
                    frameborder="0" 
                    scrolling="yes"
                    title="Best College Project 2024 Logo">
            </iframe>
        </div>
    </section>
    
    <?php include __DIR__ . "/chatbot_button.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper', {
            autoplay: { delay: 4000, disableOnInteraction: false },
            loop: true,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        });
    </script>
</body>
</html>