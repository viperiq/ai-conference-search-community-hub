<?php
session_start();

// Include the database connection file
$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

// Handle search term
$searchTerm = isset($_GET['search_term']) ? $_GET['search_term'] : '';

// Perform the search using the centralized helper function
$searchResults = searchEvents($mysqli, $_GET);

// Check if any results are found
if (!$searchResults) {
    die("Error in query: " . $mysqli->error);
}

// Now $searchResults contains the search results filtered by the conditions

// Filter function
if (isset($_SESSION["user_id"])) {
    $user = getUserProfile((int)$_SESSION["user_id"], $mysqli);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Events | ConfHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50">

    <?php include __DIR__ . '/navbar.php'; ?>

    <h2 class="cc">Search For Other Events</h2>
    <form method="get" action="events_search.php" class="search-form">
        <input class="searchBar" type="text" name="search_term" id="search_query" required placeholder="Search..." value="<?= htmlspecialchars($searchTerm) ?>"> 
        <button type="submit" class="search_button">Search</button>
    </form>
    <!-- Filter Form -->
    <form method="get" action="events_search.php" id="event-form">
   
            <label for="event_type">Type:</label>
            <select name="event_type" id="event_type">
                <option value="">Any Type</option>
                <option value="Conference">Conference</option>
                <option value="Seminar">Seminar</option>
                <option value="Workshop">Workshop</option>
            </select>

            <label for="topic">topic:</label>
            <select name="topic" id="topic">
                <option value="">Any topic</option>
                <option value="BIO">BIO</option>
                <option value="IT">IT</option>
            </select>
            
            <label for="country">Country:</label>
            <select name="country" id="country">
                <option value="">Any Country</option>
                <option value="iraq">Iraq</option>
                <option value="USA">USA</option>
                <option value="UK">UK</option>
                <option value="Canada">Canada</option>
            </select>

            <label for="start_date_from">Start Date From:</label>
            <input type="date" name="start_date_from" id="start_date_from">

            <label for="start_date_to">To:</label>
            <input type="date" name="start_date_to" id="start_date_to">

            <button type="submit">Apply Filter</button>
        
    </form>

    <div class="max-w-4xl mx-auto px-6 py-12">
        <h2 class="text-2xl font-bold text-slate-900 mb-8 flex items-center">
            <span class="w-2 h-8 bg-emerald-500 rounded-full mr-4"></span>
            Event Search Results
        </h2>

        <?php if (!empty($searchResults)): ?>
            <div class="space-y-6">
                <?php foreach ($searchResults as $result): ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 transition hover:shadow-md">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-extrabold text-slate-900 leading-tight">
                                <a href="event_details.php?event_id=<?= $result['id'] ?>" class="hover:text-indigo-600"><?= htmlspecialchars($result["event_name"]) ?></a>
                            </h3>
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase rounded-full tracking-widest"><?= htmlspecialchars($result["event_type"]) ?></span>
                        </div>
                        
                        <p class="text-slate-500 text-sm mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Starts on: <?= htmlspecialchars($result["start_date"]) ?>
                        </p>
                        
                        <p class="text-slate-600 leading-relaxed mb-6 italic text-sm line-clamp-2"><?= htmlspecialchars($result["event_description"]) ?></p>
                    <!-- Display Likes -->
                    <?php
                    $eventId = $result['id'];
                    $likesStmt = $mysqli->prepare("SELECT COUNT(*) as like_count FROM event_likes WHERE event_id = ?");
                    $likesStmt->bind_param("i", $eventId);
                    $likesStmt->execute();
                    $likesResult = $likesStmt->get_result();
                    $likeCount = ($likesResult) ? $likesResult->fetch_assoc()['like_count'] : 0;
                    ?>
                    <!-- Like Form -->
                    <div class="post-footer">
                    <form method="post" action="addc.php">
                        <input type="hidden" name="event_id" value="<?= $result['id'] ?>">
                        <button type="submit" name="like">like(<?= $likeCount ?>)</button>
                    </form>
                    
                    <!-- Toggle Comment Button -->
                    <button class="toggle-comment">Comments</button>
                    </div>
                    <!-- Comment Form -->
                    
                    <form class="comment-form" method="post" action="addc.php" style="display: none;">
                        <input type="hidden" name="event_id" value="<?= $result['id'] ?>">
                        <input name="comment_text" placeholder="Add your comment" required>
                        <button type="submit" name="comment">Add Comment</button>
                    </form>

                    <!-- Display Comments -->
                    <div class="comments-container" style="display: none;">
                        <h4>Comments:</h4>
                        <?php
                        $commStmt = $mysqli->prepare("SELECT ec.*, u.name FROM event_comments ec JOIN user u ON ec.user_id = u.id WHERE ec.event_id = ? ORDER BY ec.created_at DESC");
                        $commStmt->bind_param("i", $eventId);
                        $commStmt->execute();
                        $commentsResult = $commStmt->get_result();

                        if ($commentsResult) {
                            while ($comment = $commentsResult->fetch_assoc()) {
                                echo "<p><strong>" . htmlspecialchars($comment['name']) . ":</strong> " . htmlspecialchars($comment['comment_text']) . "</p>";
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
<!-- Country tags container -->
<div class="containere">
   <div class="tags">

<div class="panel">
    <h3>Country:</h3>
    <hr>
  
        <?php
        $countryTagsQuery = "SELECT DISTINCT country, COUNT(*) as event_count FROM events GROUP BY country";
        $countryTagsResult = $mysqli->query($countryTagsQuery);

        if ($countryTagsResult) {
            while ($row = $countryTagsResult->fetch_assoc()) {
                $country = htmlspecialchars($row['country']);
                $eventCount = $row['event_count'];
                echo "<a class='tag' href='events_search.php?country=$country'>$country<span class='tag-count'>$eventCount</span></a>";
            }
        }
        ?>
</div>
<div class="panel">
    <h3>Topic:</h3>
<hr>
        <?php
        $topicTagsQuery = "SELECT DISTINCT topic, COUNT(*) as event_count FROM events GROUP BY topic";
        $topicTagsResult = $mysqli->query($topicTagsQuery);

        if ($topicTagsResult) {
            while ($row = $topicTagsResult->fetch_assoc()) {
                $topic = htmlspecialchars($row['topic']);
                $eventCount = $row['event_count'];
                echo "<a class='tag' href='events_search.php?topic=$topic'>$topic<span class='tag-count'>$eventCount</span></a>";
            }
        }
        ?>
    </div>
    <div class="panel">
    <h3>Type:</h3>
 <hr>
        <?php
        $typeTagsQuery = "SELECT DISTINCT event_type, COUNT(*) as event_count FROM events GROUP BY event_type";
        $typeTagsResult = $mysqli->query($typeTagsQuery);

        if ($typeTagsResult) {
            while ($row = $typeTagsResult->fetch_assoc()) {
                $eventType = htmlspecialchars($row['event_type']);
                $eventCount = $row['event_count'];
                echo "<a class='tag' href='events_search.php?event_type=$eventType'>$eventType<span class='tag-count'>$eventCount</span></a>";
            }
        }
        ?>
</div>
</div>
</div>
        <!-- Jquery needed -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="js/scripts.js"></script>
<script>
    $(document).ready(function () {
        // Toggle Comment Button click event
        $(".toggle-comment").click(function () {
            // Toggle the associated comment form and comments container
            var container = $(this).closest(".event-result");
            container.find(".comment-form").toggle();
            container.find(".comments-container").toggle();
        });
    });
</script>

    <!-- Other scripts and styles -->
    <script>
    $(document).ready(function () {
        $('.navTrigger').click(function () {
            $(this).toggleClass('active');
            $("#mainListDiv").toggleClass("show_list");
            $("#mainListDiv").fadeIn();
        });
    });

    $(window).scroll(function () {
        if ($(document).scrollTop() > 50) {
            $('.nav').addClass('affix');
            console.log("OK");
        } else {
            $('.nav').removeClass('affix');
        }
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