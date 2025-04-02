<?php
include 'auth.php'; // Ensure only authorized users can access
require_once('../lib/function.php');

$db = new db_functions();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user from the database
    if ($db->deleteUser($user_id)) {
        header("Location: manage_users.php?success=User deleted successfully");
        exit();
    } else {
        header("Location: manage_users.php?error=Failed to delete user");
        exit();
    }
} else {
    header("Location: manage_users.php?error=Invalid request");
    exit();
}
?>
