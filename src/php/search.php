<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";

    $searchtext = $_GET['searchText'];
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $sql = "
            SELECT DISTINCT *, lied.coverPath
            FROM lied 
            INNER JOIN komponieren ON lied.USID = komponieren.USID
            INNER JOIN kuenstler ON komponieren.UArtID = kuenstler.UArtID
            INNER JOIN enthalten ON lied.USID = enthalten.USID
            INNER JOIN album ON enthalten.UAlbID = album.UAlbID
            LEFT JOIN beinhalten ON lied.USID = beinhalten.USID
            LEFT JOIN playlist ON playlist.UPID = beinhalten.UPID
            WHERE lied.songName LIKE '%$searchtext%'
            OR kuenstler.artistName LIKE '%$searchtext%'
            OR lied.genre LIKE '%$searchtext%'
            OR album.albumName LIKE '%$searchtext%'
            OR lied.releaseDate LIKE '%$searchtext%'
            OR playlist.playlistName LIKE '%$searchtext%'
            GROUP BY lied.USID
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