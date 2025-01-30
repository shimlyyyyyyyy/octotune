<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $usid = $_GET['USID'];
        $upid = $_GET['UPID'];

        $sql = "
            INSERT INTO beinhalten (USID, UPID)
            VALUES ('$usid', '$upid')";
        $conn->exec($sql);
    }
    catch (Exception $e) {
        echo "$e";
    }
?>