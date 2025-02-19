<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $uuid = $_COOKIE['uuid'];
        
        try {
            $conn->exec("DELETE FROM speichern WHERE UWID IN (SELECT UWID FROM wiedergabeverlauf WHERE UUID = '$uuid')");
        }
        try {
            $conn->exec("DELETE FROM wiedergabeverlauf WHERE UUID = '$uuid'");
        } 
        try {
            $conn->exec("DELETE FROM beinhalten WHERE UPID IN (SELECT UPID FROM playlist WHERE createdBy = '$uuid')");
        } 
        try {
            $conn->exec("DELETE FROM erstellen WHERE UUID = '$uuid'");
        } 
        try {
            $conn->exec("DELETE FROM playlist WHERE createdBy = '$uuid'");
        } 
        try {
            $conn->exec("DELETE FROM benutzer WHERE UUID = '$uuid'");
        } 
        
        unset($_COOKIE['uuid']);
    }
    catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
?>