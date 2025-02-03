<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        $uuid = $_COOKIE['uuid'];
        $playlistname = $_GET['playlistname'];

        $sql = "
            INSERT INTO playlist (playlistName, createdOn, isPublic, createdBy) 
            VALUES ('$playlistname', '". DATE("Y-m-d") ."', 'f' , '$uuid')";
        $conn->exec($sql);

        $newUPID = $conn->lastInsertId();
        
        $sql = "
            INSERT INTO erstellen (UUID, UPID)
            VALUES ('$uuid', '$newUPID')";

        $conn->exec($sql);
    }
    catch (Exception $e) {
        echo "$e";
    }
?>