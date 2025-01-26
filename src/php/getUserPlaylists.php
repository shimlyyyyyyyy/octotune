<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        $uuid = $_COOKIE['uuid'];
        $sql = "
            SELECT * 
            FROM playlist inner join erstellen 
            ON playlist.UPID = erstellen.UPID
            inner join benutzer
            ON erstellen.UUID = benutzer.UUID
            WHERE benutzer.UUID = '$uuid'
            ";	
        $result = $conn->query($sql);
        
        $playlists = array();
        foreach ($result as $row) {
            array_push($playlists, $row);
        }

        echo json_encode($playlists);

    } catch (Exception $e) {
        echo json_encode(array("error" => "An error occurred: " . $e->getMessage()));
    }
?>