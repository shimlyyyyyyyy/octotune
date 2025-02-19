<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $upid = $_GET['UPID'];
        $uuid = $_COOKIE['uuid'];

        $sql = "
            DELETE FROM beinhalten
            WHERE UPID = '$upid'";
        $conn->exec($sql);
        
        $sql = "
            DELETE FROM erstellen
            WHERE UUID = '$uuid' 
            AND UPID = '$upid'
        ";
        $conn->exec($sql);


        $sql = "
            DELETE FROM playlist
            WHERE UPID = '$upid'
        ";
        $conn->exec($sql);
    }
    catch (Exception $e) {
        echo (string)$e;
    }
?>