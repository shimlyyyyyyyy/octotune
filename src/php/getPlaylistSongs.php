<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        $upid = $_GET['UPID'];

        $sql = "
            SELECT * 
            FROM playlist inner join beinhalten
            on playlist.UPID = beinhalten.UPID
            inner join lied
            on beinhalten.USID = lied.USID
            where playlist.UPID = '$upid'";
        $result = $conn->query($sql);
        
        $songs = array();
        foreach ($result as $row) {
            array_push($songs, $row);
        }
        echo json_encode($songs);
    }
    catch (Exception $e) {
        echo "$e";
    }
?>