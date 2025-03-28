<?php
header("Content-Type: application/json");
require_once('../lib/function.php');
$db = new db_functions();

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);
$helper = $db->getHelperById($id); // Ensure this function exists

if ($helper) {
    echo json_encode($helper);
} else {
    echo json_encode(['error' => 'Helper not found']);
}
?>
