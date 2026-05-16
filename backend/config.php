<?php
// backend/config.php

$host = 'localhost';
$dbname = 'dhl_incidents';
$username = 'root'; 
$password = ''; 

// Gemini API Configuration
$gemini_api_key = 'AIzaSyCDMeF5I-NGgOa9gAYoP5N57AdhfBPMNvg'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $e->getMessage()
    ]));
}
?>
