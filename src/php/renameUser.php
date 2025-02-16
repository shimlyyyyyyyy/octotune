<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
   
    $newname = $_GET['newUsername'];
    $uuid = $_COOKIE['uuid'];
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);

        
        $sql = "UPDATE benutzer SET username = '$newname' WHERE UUID = '$uuid'";
        $conn->exec($sql);
        
    } catch (Exception $e) {
        echo json_encode(array("error" => "An error occurred: " . $e->getMessage()));
    }
?>