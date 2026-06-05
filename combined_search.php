<?php 
session_start();
$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/search.php";

$user = null;
if (isset($_SESSION["user_id"])) {
    $user = getUserProfile((int)$_SESSION["user_id"], $mysqli);
}

// Handle search term
$searchTerm = isset($_GET['search_term']) ? $_GET['search_term'] : '';

// Perform the database search using the centralized helper function
$searchResultsDatabase = searchEvents($mysqli, $_GET);

// Get sort parameters for Google search
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date:d';
$lr = isset($_GET['lr']) ? $_GET['lr'] : 'lang_ar';

// Call the searchConferences function for Google search results
$searchResultsGoogle = searchConferences($searchTerm, 1, $lr, $sort);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combined Search Results</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="max-w-4xl mx-auto px-6 py-12">
        <div class="text-center mb-10">
            <img src="images/logo2.png" alt="ConfHub Logo" class="mx-auto h-20 mb-4">
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Combined Search Results</h1>
            <p class="text-slate-500 mt-2">Showing results from our database and Google.</p>
        </div>

        <h2 class="text-2xl font-bold text-slate-900 mb-8 flex items-center">
            <span class="w-2 h-8 bg-emerald-500 rounded-full mr-4"></span>
            Internal Database Results
        </h2>
        <?php if ($searchResultsDatabase->num_rows > 0): ?>
            <div class="space-y-6 mb-10">
                <?php while ($result = $searchResultsDatabase->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition hover:shadow-md">
                        <h3 class="text-lg font-bold text-indigo-600 hover:underline mb-2">
                            <a href="event_details.php?event_id=<?= $result['id'] ?>"><?= htmlspecialchars($result["event_name"]) ?></a>
                        </h3>
                        <p class="text-slate-600 text-sm mb-1">Type: <span class="font-medium"><?= htmlspecialchars($result["event_type"]) ?></span></p>
                        <p class="text-slate-600 text-sm mb-1">Start Date: <span class="font-medium"><?= htmlspecialchars($result["start_date"]) ?></span></p>
                        <p class="text-slate-600 text-sm leading-relaxed line-clamp-2">Description: <?= htmlspecialchars($result["event_description"]) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl p-20 text-center border-2 border-dashed border-slate-200 mb-10">
                <p class="text-slate-400 font-medium">No internal database results found.</p>
            </div>
        <?php endif; ?>
        <p class="text-center text-slate-500 text-sm mb-12">
            Didn't find what you were looking for? Explore more results in the original search: 
            <a href="events_search.php?search_term=<?= urlencode($searchTerm ) ?>" class="text-indigo-600 font-bold hover:underline">click here</a>
        </p>

        <div class="text-center mb-10">
            <img src="images/mixlogo.png" alt="Google Logo" class="mx-auto h-16 mb-4">
            <h2 class="text-2xl font-bold text-slate-900 flex items-center justify-center">
                <span class="w-2 h-8 bg-emerald-500 rounded-full mr-4"></span>
                Google Search Results
            </h2>
        </div>
        <?php if (!empty($searchResultsGoogle)): ?>
            <div class="space-y-6">
                <?php foreach ($searchResultsGoogle as $result): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition hover:shadow-md">
                        <div class="result-title mb-2" onclick="showPopup('<?= htmlspecialchars($result['title']) ?>', '<?= htmlspecialchars($result['link']) ?>')">
                            <h3 class="text-lg font-bold text-indigo-600 hover:underline"><a href="<?= htmlspecialchars($result['link']) ?>" target="_blank"><?= htmlspecialchars($result['title']) ?></a></h3>
                        </div>
                        <?php if (isset($result['snippet'])): ?>
                            <p class="text-slate-600 text-sm leading-relaxed mb-4">Snippet: <?= htmlspecialchars($result['snippet']) ?></p>
                        <?php endif; ?>
                        <?php if (isset($result['image']) && $result['image'] !== 'placeholder-image.png'): ?>
                            <img class="w-full h-48 object-cover rounded-xl mt-4 border border-slate-100" src="<?= htmlspecialchars($result['image']) ?>" alt="Result Image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl p-20 text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-medium">No Google search results found.</p>
            </div>
        <?php endif; ?>

        <p class="text-center text-slate-500 text-sm mt-12">
            Didn't find what you were looking for? Explore more results in our Google search: 
            <a href="index.php" class="text-indigo-600 font-bold hover:underline">click here</a>
        </p>
    </div>

    <script>
        function showPopup(title, link) {
            alert(`Title: ${title}\nLink: ${link}`);
        }
    </script>
    <?php include __DIR__ . "/chatbot_button.html"; ?>
</body>
</html>
