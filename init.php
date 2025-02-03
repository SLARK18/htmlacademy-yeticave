<?php
try {
    $con = new PDO("mysql:host=localhost;dbname=yeticave;charset=utf8mb4", "root", "GOAL", [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $bad_con = false;
} catch (PDOException $e) {
    $bad_con = $e->getMessage();
}

