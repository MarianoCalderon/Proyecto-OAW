<?php
require 'Parser.php';
require 'CacheHandler.php';
require 'DB.php';

function add_channel($url) {
    $conn = get_db_conn();
    if (!$conn) return false;

    try {
        // Validate and parse the RSS feed
        $feedData = parse_feed($url);
        if (!$feedData) return false;
        
        // Check if channel already exists
        $query = $conn->prepare("SELECT id FROM channels WHERE feed_url = ?");
        $query->execute([$url]);
        if ($query->fetch()) {
            return false;
        }

        // Insert new channel
        $query = $conn->prepare("
            INSERT INTO channels (title, description, feed_url)
            VALUES (?, ?, ?)
        ");

        $query->execute([
            $feedData['channel']['title'],
            $feedData['channel']['description'],
            $url
        ]);

        return true;
    } catch (PDOException $e) {
        return false;
    }finally{
        $conn = null;
    }
}

function update_news() {
    $conn = get_db_conn();
    if (!$conn) return false;

    try {
        // Get all channels
        $channels = $conn->query("SELECT id, feed_url FROM channels")->fetchAll();

        foreach ($channels as $channel) {
            $feedData = parse_feed($channel['feed_url']);
            
            if (!isset($feedData['item']) || empty($feedData['item'])) continue;

            $conn->beginTransaction();
            
            foreach ($feedData['item'] as $item) {
                try {
                    // Check if item already exists
                    $query = $conn->prepare("SELECT id FROM feed_items WHERE link = ?");
                    $query->execute([$item['link']]);
                    if ($query->fetch()) continue;

                    // Insert new item
                    $query = $conn->prepare("
                        INSERT INTO feed_items 
                        (channel_id, title, description, link, pub_date)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $query->execute([
                        $channel['id'],
                        $item['title'],
                        $item['description'],
                        $item['link'],
                        $item['pubDate']
                    ]);

                } catch (PDOException $e) {
                    error_log("Error inserting item: " . $e->getMessage());
                }
            }
            $conn->commit();
        }

        clearSearchCache();
        return true;

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Update failed: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

function get_news($sortBy = 'pub_date') {
    $conn = get_db_conn();
    if (!$conn) return [];

    $sortColumn = 'pub_date';
    if ($sortBy === 'title') {
        $sortColumn = 'title';
    } elseif ($sortBy === 'description') {
        $sortColumn = 'description';
    }

    try {
        $query = $conn->query("
            SELECT 
                f.id, 
                f.title, 
                f.description, 
                f.link, 
                f.pub_date,
                c.title AS channel_title
            FROM feed_items f
            JOIN channels c ON f.channel_id = c.id
            ORDER BY f.$sortColumn DESC
            LIMIT 50
        ");

        return $query->fetchAll();

    } catch (PDOException $e) {
        error_log("Failed to fetch news: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

function search_news($search) {
    $conn = get_db_conn();
    if (!$conn) return [];

    // Search cache to see if the result exists
    $cachedResult = getCachedSearch($search); 
    if($cachedResult != null){
        return $cachedResult;
    }

    try {
        $query = $conn->prepare("
            SELECT 
                f.id, 
                f.title, 
                f.description, 
                f.link, 
                f.pub_date,
                c.title AS channel_title,
                MATCH(f.description, f.title) AGAINST (? IN NATURAL LANGUAGE MODE) AS score
            FROM feed_items f
            JOIN channels c ON f.channel_id = c.id
            WHERE MATCH(f.description, f.title) AGAINST (? IN NATURAL LANGUAGE MODE)
            ORDER BY score DESC
            LIMIT 50
        ");

        $query->execute([$search, $search]);

        $result = $query->fetchAll();

        cacheSearch($search, $result);
        return $result;
    } catch (PDOException $e) {
        echo $e->getMessage();
        error_log("Search failed: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

function get_channels() {
    $conn = get_db_conn();
    if (!$conn) return [];

    try {
        $stmt = $conn->query("SELECT title FROM channels");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Failed to fetch channels: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}
?>