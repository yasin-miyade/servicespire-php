<?php
include 'auth.php'; // Ensure only authorized users can access
require_once('../lib/function.php');

$db = new db_functions();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $helper_id = $_GET['id'];

    // Delete helper from the database
    if ($db->deleteHelper($helper_id)) {
        header("Location: manage_helpers.php?success=Helper deleted successfully");
        exit();
    } else {
        header("Location: manage_helpers.php?error=Failed to delete helper");
        exit();
    }
} else {
    header("Location: manage_helpers.php?error=Invalid request");
    exit();
}
?>
