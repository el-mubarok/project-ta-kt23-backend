<?php
$servername = "localhost";
$username = "u349600776_ish";
$password = "Hostinger2023";

try {
  $conn = new PDO(
    "mysql:host=$servername;dbname=self_project_attendance", 
    $username, 
    $password
  );
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
