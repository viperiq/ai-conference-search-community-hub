<?php
session_start();

// 1. Load the database connection and capture the $mysqli object
$mysqli = require __DIR__ . "/database.php";

// 2. Load common helper functions
require_once __DIR__ . "/functions.php";

$user = null;
if (isset($_SESSION["user_id"])) {
    $user = getUserProfile((int)$_SESSION["user_id"], $mysqli);
}

// Include the searchConferences function
require_once __DIR__ . "/search.php";


// Check if a search query is submitted
if (isset($_GET['q'])) {
    $query = urlencode($_GET['q']);

    // Call the searchConferences function
    $results = searchConferences($query);

    // Redirect to the search results page
    header("Location: search_results.php?q=$query");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Search | ConfHub</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">

    <?php require_once __DIR__ . "/navbar.php"; ?>

    <main class="flex-1 flex flex-col items-center justify-center text-center px-6">
        <div class="max-w-2xl w-full">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 mb-4">
                Google Conferences Search
            </h1>
            <p class="text-slate-500 mb-10 max-w-lg mx-auto">Discover academic conferences and papers from across the web.</p>
            <form action="index.php" method="get" class="bg-white rounded-full shadow-lg border border-slate-200 p-2 flex">
                <input type="text" id="search_query" name="q" required placeholder="Search for topics like 'AI in healthcare'..." class="flex-1 bg-transparent px-6 text-lg outline-none">
                <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-full font-bold hover:bg-indigo-700 transition">Search</button>
            </form>
        </div>
    </main>

</body>
</html>
