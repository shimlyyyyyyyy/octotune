<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $usid = $_GET['USID'];
        $upid = $_GET['UPID'];

        // Get the current maximum order value for the playlist
        $sql = "SELECT MAX(`order`) as max_order FROM beinhalten WHERE UPID = '$upid'";
        $result = $conn->query($sql);
        $row = $result->fetch();
        $maxOrder = $row['max_order'] !== null ? $row['max_order'] : 0;
        $newOrder = $maxOrder + 1;

        // Insert the new song with the next order value
        $sql = "
            INSERT INTO beinhalten (USID, UPID, `order`)
            VALUES ('$usid', '$upid', '$newOrder')";
        $conn->exec($sql);
    }
    catch (Exception $e) {
        echo "$e";
    }
?>