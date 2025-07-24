<?php

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_nunsdimsum";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// Include helper functions
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/navigation.php';

// Additional configuration can go here

