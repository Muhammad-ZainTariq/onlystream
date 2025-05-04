<?php
session_start();
require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - OnlyStream</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="container">
        <div class="search-results">
            <?php
            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                $searchQuery = trim($_GET['search']);
                $results = search_content($pdo, $searchQuery);

                if (!empty($results)) {
                    echo '<h2>Search Results for "' . htmlspecialchars($searchQuery) . '"</h2>';
                    foreach ($results as $result) {
                        $link = $result['type'] === 'Video' 
                            ? "retreive_videos.php?id=" . $result['item_id'] 
                            : "display_audio.php?id=" . $result['item_id'];
                        echo '<div class="result-item">';
                        echo '<span>' . htmlspecialchars($result['title']) . ' (' . $result['type'] . ')</span>';
                        echo '<a href="' . $link . '" class="action-button action-button--submit">View</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<h2>No results found for "' . htmlspecialchars($searchQuery) . '"</h2>';
                    echo '<p>Try a different search term.</p>';
                }
            } else {
                echo '<h2>Please enter a search query.</h2>';
            }
            ?>
        </div>
    </div>
</body>
</html>