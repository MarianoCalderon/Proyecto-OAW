<?php
function parse_feed($feedUrl) {
    try {
        // Fetch the feed content using file_get_contents to validate we can access the url
        $feedContent = @file_get_contents($feedUrl);
        if (!$feedContent)  return null;

        // Load the RSS feed using DOMDocument
        $feed = new DOMDocument();
        @$feed->load($feedUrl);
        
        // Extract channel information (Website info)
        $channel = $feed->getElementsByTagName('channel')->item(0);
        if (!$channel) return null;
        
        $result['channel'] = [
            'title' => $channel->getElementsByTagName('title')->item(0)->nodeValue,
            'description' => $channel->getElementsByTagName('description')->item(0)->nodeValue,
            'link' => $channel->getElementsByTagName('link')->item(0)->nodeValue,
        ];
        
        // Extract items (Individual news pieces)
        $items = $feed->getElementsByTagName('item');
        if (!$items) return null;

        foreach ($items as $item) {
            // Get date in the correct format
            $pubDate = $item->getElementsByTagName('pubDate')->item(0)->nodeValue;
            $dateTime = new DateTime($pubDate);
            $dateOnly = $dateTime->format("Y-m-d");

            $result['item'][] = [
                'title' => $item->getElementsByTagName('title')->item(0)->nodeValue,
                'description' => $item->getElementsByTagName('description')->item(0)->nodeValue,
                'pubDate' => $dateOnly,
                'link' => $item->getElementsByTagName('link')->item(0)->nodeValue,
            ];
        }

        return $result;
    } catch (Exception $e) {
        return null;
    }
}
?>