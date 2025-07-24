<?php
session_start();

// Include configuration untuk helper functions
require_once __DIR__ . '/src/includes/config.php';

// Logout menggunakan helper function
logout();
?>
