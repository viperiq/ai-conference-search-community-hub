<?php
session_start();
// 1. Load the database connection and capture the $mysqli object
$mysqli = require __DIR__ . "/database.php";

// 2. Load common helper functions
require_once __DIR__ . "/functions.php";

// Check for query parameter
if (isset($_GET['q'])) {
    $query = $_GET['q'];

    // Check for page parameter
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

    // Include the searchConferences function
    include 'search.php';

    // Get sort parameter if available
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date:d';
    $lr = isset($_GET['lr']) ? $_GET['lr'] : 'lang_ar';
    // Call the searchConferences function with page parameter and sort parameter
    $results = searchConferences($query, $page, $lr, $sort);
}

$user = null;
if (isset($_SESSION["user_id"])) {
    $user = getUserProfile((int)$_SESSION["user_id"], $mysqli);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    <?php include __DIR__ . '/navbar.php'; ?>

    <form action="" method="get" class="max-w-4xl mx-auto px-6 py-8 flex space-x-4">
        <input type="text" id="search_query" name="q" placeholder="Enter your search..." value="<?= htmlspecialchars($query) ?>"
               class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition">Search</button>
    </form>

    <form action="" method="get" class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between bg-white rounded-xl shadow-sm border border-slate-200 mt-6">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <label for="sort" class="text-sm font-medium text-slate-700">Date:</label>
                <select id="sort" name="sort" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="date:a" <?= ($sort == 'date:a') ? 'selected' : '' ?>>Ascending</option>
                    <option value="date:d" <?= ($sort == 'date:d') ? 'selected' : '' ?>>Descending</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label for="lr" class="text-sm font-medium text-slate-700">Language:</label>
                <select id="lr" name="lr" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="lang_ar" <?= ($lr == 'lang_ar') ? 'selected' : '' ?>>Arabic</option>
                    <option value="lang_en" <?= ($lr == 'lang_en') ? 'selected' : '' ?>>English</option>
                    <option value="lang_fr" <?= ($lr == 'lang_fr') ? 'selected' : '' ?>>French</option>
                    <option value="lang_de" <?= ($lr == 'lang_de') ? 'selected' : '' ?>>German</option>
                    <option value="lang_es" <?= ($lr == 'lang_es') ? 'selected' : '' ?>>Spanish</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
        <button type="submit" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition">Apply Filters</button>
    </form>

    <div class="max-w-4xl mx-auto px-6 py-8 space-y-6">
        <h2 class="text-2xl font-bold text-slate-900 flex items-center">
            <span class="w-2 h-8 bg-emerald-500 rounded-full mr-4"></span>
            Google Search Results
        </h2>
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $result): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition hover:shadow-md">
                    <div class="result-title mb-2" onclick="showPopup('<?= htmlspecialchars($result['title']) ?>', '<?= htmlspecialchars($result['link']) ?>')">
                        <h3 class="text-lg font-bold text-indigo-600 hover:underline"><a href="<?= htmlspecialchars($result['link']) ?>" target="_blank"><?= htmlspecialchars($result['title']) ?></a></h3>
                    </div>
                    <?php if (isset($result['snippet'])): ?>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4"><?= htmlspecialchars($result['snippet']) ?></p>
                    <?php endif; ?>
                    <?php if (isset($result['image']) && $result['image'] !== 'placeholder-image.png'): ?>
                        <img class="w-full h-48 object-cover rounded-xl mt-4 border border-slate-100" src="<?= htmlspecialchars($result['image']) ?>" alt="Result Image">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-2xl p-20 text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-medium">No results found.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function showPopup(title, link) {
        // Create a pop-up alert with title and link information
        alert(`Title: ${title}\nLink: ${link}`);
    }
    </script>
    
    <div class="max-w-4xl mx-auto px-6 py-8 flex justify-center items-center space-x-4">
        <?php if ($page > 1): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>&sort=<?= $sort ?>&lr=<?= $lr ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-100 transition">Previous Page</a>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
            <span class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-700">Page <?= $page ?></span>
        <?php endif; ?>

        <?php if (count($results) == 10): // Assuming 10 results per page ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>&sort=<?= $sort ?>&lr=<?= $lr ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-100 transition">Next Page</a>
        <?php endif; ?>
    </div>
</body>
</html>
