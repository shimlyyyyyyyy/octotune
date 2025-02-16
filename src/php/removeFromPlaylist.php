<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $upid = $_GET['UPID'];
        $usid = $_GET['USID'];
        
        $sql = "
            DELETE FROM beinhalten
            WHERE USID = '$usid'
            AND UPID = '$upid' 
        ";
        $conn->exec($sql);

    }
    catch (Exception $e) {
        echo (string)$e;
    }
?>