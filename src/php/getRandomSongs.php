

<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $sql = "SELECT * FROM lied ORDER BY RAND() LIMIT 10";
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