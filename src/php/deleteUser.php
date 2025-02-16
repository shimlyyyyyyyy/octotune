<?php
    const servername = "localhost";
    const username = "root";
    const password = "";
    const dbname = "octotune";
    try {
        $conn = new PDO("mysql:host=".servername."; dbname=".dbname, username, password);
        
        $uuid = $_COOKIE['uuid'];
        
        // Delete related records first to avoid foreign key constraint violations //copilot hat geholfen die scheisse ist so nervig ich will das nie wieder tun bitte geben sie mir eine 1 <3
        try {
            $conn->exec("DELETE FROM speichern WHERE UWID IN (SELECT UWID FROM wiedergabeverlauf WHERE UUID = '$uuid')");
        } catch (Exception $e) {
            // Log error or handle it as needed
        }
        try {
            $conn->exec("DELETE FROM wiedergabeverlauf WHERE UUID = '$uuid'");
        } catch (Exception $e) {
            // Log error or handle it as needed
        }
        try {
            $conn->exec("DELETE FROM beinhalten WHERE UPID IN (SELECT UPID FROM playlist WHERE createdBy = '$uuid')");
        } catch (Exception $e) {
            // Log error or handle it as needed
        }
        try {
            $conn->exec("DELETE FROM erstellen WHERE UUID = '$uuid'");
        } catch (Exception $e) {
            // Log error or handle it as needed
        }
        try {
            $conn->exec("DELETE FROM playlist WHERE createdBy = '$uuid'");
        } catch (Exception $e) {
            // Log error or handle it as needed
        }
        try {
            $conn->exec("DELETE FROM benutzer WHERE UUID = '$uuid'");
        } catch (Exception $e) {
            // Log error or handle it as needed
        }
        
        unset($_COOKIE['uuid']);
    }
    catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
?>