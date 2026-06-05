<?php
session_start();

$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

// Check if the event ID is provided in the URL
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    header("Location: index.php"); // Redirect to the main page if no valid event ID is provided
    exit();
}

// Retrieve the event details from the database
$eventId = $_GET['event_id'];
$eventQuery = "SELECT * FROM events WHERE id = ?";
$stmt = $mysqli->prepare($eventQuery);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

// Check if the event exists
if (!$result || $result->num_rows === 0) {
    header("Location: index.php"); // Redirect to the main page if the event doesn't exist
    exit();
}

$event = $result->fetch_assoc();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user information
$user_id = $_SESSION["user_id"];
$user = getUserProfile($user_id, $mysqli);

// Retrieve posts for the logged-in user
$postsQuery = "SELECT posts.*, user.name AS user_name FROM posts
               JOIN user ON posts.user_id = user.id
               ORDER BY posts.created_at DESC";

$postsResult = $mysqli->query($postsQuery);
$posts = $postsResult ? $postsResult->fetch_all(MYSQLI_ASSOC) : [];

// Keep the mysqli connection open until you finish using it
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['event_name']) ?> Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">
    <?php include __DIR__ . '/navbar.php'; ?>

    <!-- Hero Image -->
    <div class="max-w-5xl mx-auto px-6 mt-10">
        <div class="w-full h-96 rounded-3xl overflow-hidden shadow-lg border border-slate-200">
            <img src="<?= htmlspecialchars($event['image_path']) ?>" alt="Event Hero" class="w-full h-full object-cover">
        </div>
    </div>

    <!-- Main Grid -->
    <main class="max-w-5xl mx-auto px-6 py-12 grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Left: Main Content -->
        <div class="lg:col-span-8 space-y-10">
            <header>
                <span class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full uppercase tracking-widest mb-4 italic">#<?= htmlspecialchars($event['topic']) ?></span>
                <h1 class="text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6"><?= htmlspecialchars($event['event_name']) ?></h1>
                
                <div class="flex items-center space-x-4">
                    <img src="https://ui-avatars.com/api/?name=Event+Organizer&background=000&color=fff" class="w-12 h-12 rounded-full ring-2 ring-slate-100">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Organized by</p>
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($event['event_organized_by']) ?></p>
                    </div>
                </div>
            </header>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-4">About Event</h2>
                <div class="text-slate-600 leading-relaxed text-lg whitespace-pre-wrap">
                    <?= htmlspecialchars($event['event_description']) ?>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-6">Event Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-map-pin text-indigo-600 mb-4 block text-xl"></i>
                        <h4 class="text-xs font-bold uppercase text-slate-400 mb-1">Location</h4>
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($event['event_location']) ?></p>
                        <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($event['venue_address']) ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-envelope text-indigo-600 mb-4 block text-xl"></i>
                        <h4 class="text-xs font-bold uppercase text-slate-400 mb-1">Contact</h4>
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($event['contact_person']) ?></p>
                        <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($event['email_address']) ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-globe text-indigo-600 mb-4 block text-xl"></i>
                        <h4 class="text-xs font-bold uppercase text-slate-400 mb-1">Website</h4>
                        <p><a href="<?= htmlspecialchars($event['website_address']) ?>" target="_blank" class="text-indigo-600 font-bold hover:underline"><?= htmlspecialchars($event['website_address']) ?></a></p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <i class="fa-solid fa-building text-indigo-600 mb-4 block text-xl"></i>
                        <h4 class="text-xs font-bold uppercase text-slate-400 mb-1">Society</h4>
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($event['organizing_society']) ?></p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right: Sticky Sidebar -->
        <aside class="lg:col-span-4">
            <div class="sticky top-32 bg-white p-8 rounded-3xl border border-slate-200 shadow-xl space-y-8">
                
                <!-- Start Date Row -->
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-900">
                        <i class="fa-regular fa-calendar text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Start Date</h3>
                        <p class="text-slate-500 text-sm"><?= htmlspecialchars($event['start_date']) ?></p>
                    </div>
                </div>

                <!-- End Date Row -->
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-900">
                        <i class="fa-regular fa-calendar text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">End Date</h3>
                        <p class="text-slate-500 text-sm"><?= htmlspecialchars($event['end_date']) ?></p>
                    </div>
                </div>

                <!-- Location Row -->
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-900">
                        <i class="fa-solid fa-location-dot text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900"><?= htmlspecialchars($event['city']) ?>, <?= htmlspecialchars($event['state']) ?></h3>
                        <p class="text-slate-500 text-sm"><?= htmlspecialchars($event['venue_address']) ?></p>
                    </div>
                </div>

                <!-- Registration Button -->
                <button class="w-full py-4 bg-emerald-500 text-slate-900 font-bold rounded-2xl hover:bg-emerald-400 transition shadow-lg shadow-emerald-500/20 transform hover:-translate-y-1 active:translate-y-0" onclick="alert('Registration feature coming soon!')">
                    Register for Event
                </button>
                
                <p class="text-center text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <i class="fa-solid fa-clock"></i> Deadline: <?= htmlspecialchars($event['submission_deadline']) ?>
                </p>
            </div>
        </aside>

    </main>

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
