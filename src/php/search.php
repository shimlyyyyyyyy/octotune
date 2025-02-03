<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";

    $searchtext = $_GET['searchText'];
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $sql = "
            SELECT *, lied.coverPath
            FROM lied inner join komponieren 
            ON lied.USID = komponieren.USID
            inner join kuenstler
            ON komponieren.UArtID = kuenstler.UArtID
            inner join enthalten
            ON lied.USID = enthalten.USID
            inner join album
            ON enthalten.UAlbID = album.UAlbID
            WHERE lied.songName LIKE '%$searchtext%'
            OR kuenstler.artistName LIKE '%$searchtext%'
            OR lied.genre LIKE '%$searchtext%'
            OR album.albumName LIKE '%$searchtext%'
            OR lied.releaseDate LIKE '%$searchtext%'
            ORDER BY RAND() LIMIT 10";
        $result = $conn->query($sql);
        
        $songs = array();
        foreach ($result as $row) {
            array_push($songs, $row);
        }

        echo json_encode($songs);
    } catch (Exception $e) {
        echo json_encode(array("error" => "An error occurred: " . $e->getMessage()));
    }
?>