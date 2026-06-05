<?php

// 0. Load Composer autoloader to resolve Laravel/Illuminate types
// Note: You must run 'composer install' in this directory to create the vendor folder.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$host = "localhost";
$dbname = "login_db";
$username = "root";
$password = "";

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

// 1. Ensure core tables exist first to avoid errors during column checks
$mysqli->query("CREATE TABLE IF NOT EXISTS user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),
    topics VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

// Check if the column 'profile_image' exists in the 'user' table
$result = $mysqli->query("SHOW COLUMNS FROM user LIKE 'profile_image'");
if ($result && $result->num_rows == 0) {
    // The column doesn't exist, add it
    $alterQuery = "ALTER TABLE user ADD COLUMN profile_image VARCHAR(255)";
    if (!$mysqli->query($alterQuery)) {
        // Check if the error is due to the column already existing, in which case it's safe to ignore
        if ($mysqli->errno !== 1060) {
            die("Error adding profile_image column: " . $mysqli->error);
        }
    }
}

// Check if the table 'posts' exists before checking columns
$result = $mysqli->query("SHOW TABLES LIKE 'posts'");
if ($result && $result->num_rows == 0) {
    $createPostsTableQuery = "CREATE TABLE posts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        title VARCHAR(255),
        content TEXT,
        image_path VARCHAR(255),
        likes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id)
    )";
    $mysqli->query($createPostsTableQuery);
}

// Check if the column 'likes' exists in the 'posts' table
$result = $mysqli->query("SHOW COLUMNS FROM posts LIKE 'likes'");
if ($result && $result->num_rows == 0) {
    // The column doesn't exist, add it
    $alterQuery = "ALTER TABLE posts ADD COLUMN likes INT DEFAULT 0";
    if (!$mysqli->query($alterQuery)) {
        // Check if the error is due to the column already existing, in which case it's safe to ignore
        if ($mysqli->errno !== 1060) {
            die("Error adding likes column: " . $mysqli->error);
        }
    }
}
// Add the following lines to add the 'topics' column
$result = $mysqli->query("SHOW COLUMNS FROM user LIKE 'topics'");
if ($result && $result->num_rows == 0) {
    // The column doesn't exist, add it
    $alterQuery = "ALTER TABLE user ADD COLUMN topics VARCHAR(255)";
    if (!$mysqli->query($alterQuery)) {
        // Check if the error is due to the column already existing, in which case it's safe to ignore
        if ($mysqli->errno !== 1060) {
            die("Error adding topics column: " . $mysqli->error);
        }
    }
}

// Check if the table 'comments' exists
$result = $mysqli->query("SHOW TABLES LIKE 'comments'");
if ($result && $result->num_rows == 0) {
    $createCommentsTableQuery = "CREATE TABLE comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        post_id INT,
        content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )";
    $mysqli->query($createCommentsTableQuery);
}

// Check if the table 'likes' exists
$result = $mysqli->query("SHOW TABLES LIKE 'likes'");
if ($result && $result->num_rows == 0) {
    // The table doesn't exist, add it
    $createLikesTableQuery = "CREATE TABLE likes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        post_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id),
        FOREIGN KEY (post_id) REFERENCES posts(id)
    )";
    
    if (!$mysqli->query($createLikesTableQuery)) {
        die("Error creating likes table: " . $mysqli->error);
    }
}
// Check if the column 'title' exists in the 'posts' table
$result = $mysqli->query("SHOW COLUMNS FROM posts LIKE 'title'");
if ($result && $result->num_rows == 0) {
    // The column doesn't exist, add it
    $alterQuery = "ALTER TABLE posts ADD COLUMN title VARCHAR(255)";
    if (!$mysqli->query($alterQuery)) {
        // Check if the error is due to the column already existing, in which case it's safe to ignore
        if ($mysqli->errno !== 1060) {
            die("Error adding title column: " . $mysqli->error);
        }
    }
}

// Check if the table 'events' exists
$result = $mysqli->query("SHOW TABLES LIKE 'events'");
if ($result && $result->num_rows == 0) {
    // The table doesn't exist, add it
    $createEventsTableQuery = "CREATE TABLE events (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_name VARCHAR(255),
        event_title VARCHAR(255),
        event_type VARCHAR(255),
        topic VARCHAR(255),
        sub_topic VARCHAR(255),
        event_location VARCHAR(255),
        country VARCHAR(255),
        state VARCHAR(255),
        city VARCHAR(255),
        venue_address VARCHAR(255),
        event_organized_by VARCHAR(255),
        organizing_society VARCHAR(255),
        contact_person VARCHAR(255),
        contact_number VARCHAR(20),
        email_address VARCHAR(255),
        website_address VARCHAR(255),
        start_date DATE,
        end_date DATE,
        submission_deadline DATE,
        abstracts TEXT,
        event_description TEXT,
        image_path VARCHAR(255),
        likes INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$mysqli->query($createEventsTableQuery)) {
        die("Error creating events table: " . $mysqli->error);
    }
}
// Check if the table 'event_comments' exists
$result = $mysqli->query("SHOW TABLES LIKE 'event_comments'");
if ($result && $result->num_rows == 0) {
    // The table doesn't exist, add it
    $createEventCommentsTableQuery = "CREATE TABLE event_comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT,
        user_id INT,
        comment_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (user_id) REFERENCES user(id)
    )";
    
    if (!$mysqli->query($createEventCommentsTableQuery)) {
        die("Error creating event_comments table: " . $mysqli->error);
    }
}
// Check if the table 'event_likes' exists
$result = $mysqli->query("SHOW TABLES LIKE 'event_likes'");
if ($result && $result->num_rows == 0) {
    // The table doesn't exist, add it
    $createEventLikesTableQuery = "CREATE TABLE event_likes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_id INT,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (user_id) REFERENCES user(id)
    )";
    
    if (!$mysqli->query($createEventLikesTableQuery)) {
        die("Error creating event_likes table: " . $mysqli->error);
    }
}

return $mysqli;
