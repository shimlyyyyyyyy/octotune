<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);


        
        $usid = $_GET['USID'];
        $uuid = $_COOKIE['uuid'];
        $datetime = DATE("Y-m-d H:i:s");

        $sql = "SELECT * 
                FROM wiedergabeverlauf
                WHERE UUID = '$uuid'";
        $result = $conn->query($sql);
        $row = $result->fetch();
        $uwid = $row['UWID'];

         

        $sql = "
            INSERT INTO speichern (UWID, USID, listenedOn)
            VALUES ('$uwid', '$usid', '$datetime')";
        $conn->exec($sql);






        // Get the current maximum order value for the playlist
        
    }
    catch (Exception $e) {
        echo "$e";
    }
?>