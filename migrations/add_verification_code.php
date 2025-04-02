<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once("../lib/function.php");

// Set content type to text/html
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceSpire Database Migration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #111827;
        }
        .success {
            color: #047857;
            background-color: #ecfdf5;
            border-color: #a7f3d0;
        }
        .error {
            color: #b91c1c;
            background-color: #fee2e2;
            border-color: #fca5a5;
        }
        .info {
            color: #1e40af;
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-xl font-semibold text-gray-900 mb-6 pb-2 border-b">ServiceSpire Database Migration</h1>
            
            <div class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 mb-2">Migration Status</h2>
                
                <div class="space-y-4">
                <?php
                // Connect to database
                $db = new db_functions();
                $conn = $db->connect();
                $messages = [];
                $all_success = true;

                try {
                    // Check for each required column and add if missing
                    
                    // 1. Check for verification_code column in work_posts
                    $check_query = "SHOW COLUMNS FROM `work_posts` LIKE 'verification_code'";
                    $result = $conn->query($check_query);
                    
                    if ($result->num_rows === 0) {
                        // Column does not exist, so add it
                        $alter_query = "ALTER TABLE `work_posts` ADD COLUMN `verification_code` VARCHAR(6) NULL DEFAULT NULL COMMENT 'Verification code for job completion'";
                        
                        if ($conn->query($alter_query)) {
                            $messages[] = [
                                'type' => 'success',
                                'message' => "Successfully added 'verification_code' column to work_posts table."
                            ];
                        } else {
                            $messages[] = [
                                'type' => 'error',
                                'message' => "Failed to add 'verification_code' column: " . $conn->error
                            ];
                            $all_success = false;
                        }
                    } else {
                        $messages[] = [
                            'type' => 'info',
                            'message' => "Column 'verification_code' already exists in work_posts table."
                        ];
                    }
                    
                    // 2. Check for completion_note column in work_posts
                    $check_query = "SHOW COLUMNS FROM `work_posts` LIKE 'completion_note'";
                    $result = $conn->query($check_query);
                    
                    if ($result->num_rows === 0) {
                        // Column does not exist, so add it
                        $alter_query = "ALTER TABLE `work_posts` ADD COLUMN `completion_note` TEXT NULL DEFAULT NULL COMMENT 'Note added when completing the job'";
                        
                        if ($conn->query($alter_query)) {
                            $messages[] = [
                                'type' => 'success',
                                'message' => "Successfully added 'completion_note' column to work_posts table."
                            ];
                        } else {
                            $messages[] = [
                                'type' => 'error',
                                'message' => "Failed to add 'completion_note' column: " . $conn->error
                            ];
                            $all_success = false;
                        }
                    } else {
                        $messages[] = [
                            'type' => 'info',
                            'message' => "Column 'completion_note' already exists in work_posts table."
                        ];
                    }
                    
                    // 3. Check for completed_at column in work_posts
                    $check_query = "SHOW COLUMNS FROM `work_posts` LIKE 'completed_at'";
                    $result = $conn->query($check_query);
                    
                    if ($result->num_rows === 0) {
                        // Column does not exist, so add it
                        $alter_query = "ALTER TABLE `work_posts` ADD COLUMN `completed_at` DATETIME NULL DEFAULT NULL COMMENT 'When the job was completed'";
                        
                        if ($conn->query($alter_query)) {
                            $messages[] = [
                                'type' => 'success',
                                'message' => "Successfully added 'completed_at' column to work_posts table."
                            ];
                        } else {
                            $messages[] = [
                                'type' => 'error',
                                'message' => "Failed to add 'completed_at' column: " . $conn->error
                            ];
                            $all_success = false;
                        }
                    } else {
                        $messages[] = [
                            'type' => 'info',
                            'message' => "Column 'completed_at' already exists in work_posts table."
                        ];
                    }

                } catch (Exception $e) {
                    $messages[] = [
                        'type' => 'error',
                        'message' => "Error: " . $e->getMessage()
                    ];
                    $all_success = false;
                }

                // Display all messages
                foreach ($messages as $msg) {
                    echo '<div class="p-3 border rounded-md ' . $msg['type'] . '">';
                    echo $msg['message'];
                    echo '</div>';
                }
                ?>
                </div>
            </div>
            
            <div class="mt-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                <h3 class="font-medium text-gray-900">Migration Summary</h3>
                <p class="mt-2 text-sm text-gray-600">
                    <?php if ($all_success): ?>
                    All database migrations completed successfully. You can now use all features that depend on these database columns.
                    <?php else: ?>
                    Some migrations failed. Please check the error messages above and try again.
                    <?php endif; ?>
                </p>
                <div class="mt-4 flex">
                    <a href="../helper/pending.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Go to Pending Jobs
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
