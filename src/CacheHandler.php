<?php

    function clearSearchCache() {
        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
            return;
        }

        $files = glob(__DIR__ . '/cache/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    function getCachedSearch($search){
        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
            return null;
        }

        $fileName = md5($search);
        $cacheFile = "$cacheDir/$fileName.json";

        // 1. Return cached data if it exists and is fresh (<10 mins)
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
            return json_decode(file_get_contents($cacheFile), true);
        }else{
            return null;
        }
    }

    function cacheSearch($search, $results){
        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $fileName = md5($search);
        $cacheFile = "$cacheDir/$fileName.json";

        file_put_contents($cacheFile, json_encode($results));
    }

?>