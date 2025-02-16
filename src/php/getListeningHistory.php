<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $uuid = $_COOKIE['uuid'];

        $sql = "SELECT * 
                FROM wiedergabeverlauf
                WHERE UUID = '$uuid'";
        $result = $conn->query($sql);
        $row = $result->fetch();
        $uwid = $row['UWID'];

        

        
        $sql = "
            SELECT *
            FROM lied
            INNER JOIN speichern
            ON lied.USID = speichern.USID
            INNER JOIN wiedergabeverlauf
            ON speichern.UWID = wiedergabeverlauf.UWID
            INNER JOIN komponieren
            ON lied.USID = komponieren.USID
            INNER JOIN kuenstler
            ON komponieren.UArtID = kuenstler.UArtID
            WHERE wiedergabeverlauf.UUID = '$uuid'
            ORDER BY speichern.listenedOn DESC
        ";
        $result = $conn->query($sql);
        $row = $result->fetch();
        $songs = array();

        foreach ($result as $row) {
            array_push($songs, $row);
        }
        echo json_encode($songs);
    }
    catch (Exception $e) {
        echo (string)$e;
    }
?>