<?php
$filePath = "../user/uploads/avatar.jpg";  // Adjust the path based on actual file location

if (file_exists($filePath)) {
    echo "✅ File exists: " . realpath($filePath);
} else {
    echo "❌ File not found!";
}
?>
