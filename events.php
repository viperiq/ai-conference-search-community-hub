<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Existing code for posts...

    // Check if it's an event submission
    if (isset($_POST["event_name"]) && !empty(trim($_POST["event_name"]))) {
        // Process event form data
        $event_name = $_POST["event_name"];
        $event_title = $_POST["event_title"];
        $event_type = $_POST["event_type"];
        $topic = $_POST["topic"];
        $sub_topic = $_POST["sub_topic"];
        $event_location = $_POST["event_location"];
        $country = $_POST["country"];
        $state = $_POST["state"];
        $city = $_POST["city"];
        $venue_address = $_POST["venue_address"];
        $event_organized_by = $_POST["event_organized_by"];
        $organizing_society = $_POST["organizing_society"];
        $contact_person = $_POST["contact_person"];
        $contact_number = $_POST["contact_number"];
        $email_address = $_POST["email_address"];
        $website_address = $_POST["website_address"];
        $start_date = $_POST["start_date"];
        $end_date = $_POST["end_date"];
        $submission_deadline = $_POST["submission_deadline"];
        $abstracts = $_POST["abstracts"];
        $event_description = $_POST["event_description"];

        // Process image upload for events
        $imagePath = null;
        if (isset($_FILES["event_image"]) && $_FILES["event_image"]["error"] === UPLOAD_ERR_OK) {
            $uploadDir = 'upload1/';
            $uploadedFile = $uploadDir . uniqid() . '_' . basename($_FILES["event_image"]["name"]);

            if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $uploadedFile)) {
                $imagePath = $uploadedFile;
                
            } else {
                die("Error moving uploaded file. Check permissions and path.<br>" .
                    "Upload Error: " . $_FILES["event_image"]["error"]);
            }
        }

        // Insert event data into the database
        $insertEventQuery = "INSERT INTO events (
            event_name, event_title, event_type, topic, sub_topic, event_location,
            country, state, city, venue_address, event_organized_by,organizing_society, contact_person,
            contact_number, email_address, website_address, start_date, end_date,
            submission_deadline, abstracts, event_description, image_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

        $stmt = $mysqli->prepare($insertEventQuery);
        if ($stmt) {
            $stmt->bind_param(
                "ssssssssssssssssssssss",
                $event_name, $event_title, $event_type, $topic, $sub_topic, $event_location,
                $country, $state, $city, $venue_address, $event_organized_by,$organizing_society, $contact_person,
                $contact_number, $email_address, $website_address, $start_date, $end_date,
                $submission_deadline, $abstracts, $event_description, $imagePath
            );

            if ($stmt->execute()) {
                // Redirect or display a success message for event submission
                echo "Event added successfully!";
            } else {
                echo "Error adding event: " . $stmt->error;
            }
        } else {
            echo "Error preparing statement: " . $mysqli->error;
        }
    }
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}



// Retrieve user information
$user_id = $_SESSION["user_id"];
$user = getUserProfile($user_id, $mysqli);

// Removed unused $posts retrieval logic here to improve performance
//$mysqli->close(); // Don't close yet if you have footer includes

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event | Your Community Name</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">
<?php require_once __DIR__ . "/navbar.php"; ?>

<main class="max-w-4xl mx-auto px-6 py-12">
    <form method="post" action="" id="event-form" enctype="multipart/form-data" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-8" x-data="{ imagePreview: null }">
        <h2 class="text-center text-3xl font-bold text-slate-900">Create New Event</h2>

        <label for="event_name" class="block text-sm font-bold text-slate-700 mb-2">Event Name & Title</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="text" name="event_name" placeholder="Event Name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            <input type="text" name="event_title" placeholder="Event Title" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>

        <div class="space-y-6">
            <label class="block text-sm font-bold text-slate-700 mb-2">Categorization</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <select name="event_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">Select Type</option>
                    <option value="Conference">Conference</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Workshop">Workshop</option>
                </select>

                <select name="topic" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">Select Topic</option>   
                    <option value="BIO">BIO</option>
                    <option value="IT">IT</option>
                </select>

                <input type="text" name="sub_topic" placeholder="Sub-Topic" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <label class="block text-sm font-bold text-slate-700 mb-2">Location Details</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <select name="country" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">Select Country</option>
                    <option value="USA">USA</option>
                    <option value="UK">UK</option>
                    <option value="Canada">Canada</option>
                    <option value="iraq">Iraq</option>
                </select>
                <input type="text" name="state" placeholder="State" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                <input type="text" name="city" placeholder="City" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                <input type="text" name="venue_address" placeholder="Full Venue Address" class="md:col-span-4 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                <input type="text" name="event_location" placeholder="Location Name" class="md:col-span-2 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <label class="block text-sm font-bold text-slate-700 mb-2">Organizer Information</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="text" name="event_organized_by" placeholder="Organized By" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                <input type="text" name="organizing_society" placeholder="Organizing Society" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <input type="text" name="contact_person" placeholder="Contact Person" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                <input type="text" name="contact_number" placeholder="Contact Number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                <input type="email" name="email_address" placeholder="Email Address" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <input type="url" name="website_address" placeholder="Website URL" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <label class="block text-sm font-bold text-slate-700 mb-2">Important Dates</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label style="font-size: 1.2rem;" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Start Date</label>
                    <input type="date" name="start_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label style="font-size: 1.2rem;" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">End Date</label>
                    <input type="date" name="end_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label style="font-size: 1.2rem;" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Submission Deadline</label>
                    <input type="date" name="submission_deadline" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
            </div>

            <div>
                <label for="abstracts" class="block text-sm font-bold text-slate-700 mb-2">Abstracts</label>
                <textarea name="abstracts" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required></textarea>
            </div>

            <div>
                <label for="event_description" class="block text-sm font-bold text-slate-700 mb-2">Event Description</label>
                <textarea name="event_description" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500" required></textarea>
            </div>

            <label class="block text-sm font-bold text-slate-700 mb-2">Event Cover Image</label>
            <div class="flex items-center space-x-4">
                <label for="event_image" class="w-32 h-32 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center cursor-pointer hover:border-indigo-500 transition"
                       :style="imagePreview ? `background-image: url('${imagePreview}'); background-size: cover; background-position: center;` : ''">
                    <span x-show="!imagePreview" class="text-slate-400 text-center text-sm">Upload Image</span>
                    <input type="file" name="event_image" id="event_image" accept="image/*" class="hidden" @change="imagePreview = URL.createObjectURL($event.target.files[0])">
                </label>
                <p class="text-slate-500 text-sm">Click the box to upload an event cover image.</p>
            </div>
        </div>

        <div class="pt-8 border-t border-slate-100">
            <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">
                Add Event
            </button>
        </div>
    </form>
</main>
</body>
</html>