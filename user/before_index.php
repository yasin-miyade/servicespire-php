<?php
// This file should be included at the beginning of index.php
// to ensure proper output buffering and session management

// Start output buffering
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// No output should be sent before this point
?>
