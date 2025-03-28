<?php
// session_start();
// require_once("../lib/function.php");

// Check if the user is logged in
// if (!isset($_SESSION['email'])) {
//     die("Unauthorized access. Please log in first.");
// }

// $email = $_SESSION['email'];

// Initialize database connection
// $db = new db_functions();
// $work_posts = $db->getUserWorkPosts($email);

// Handle post deletion
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
//     $post_id = $_POST['delete_id'];
//     if ($db->deleteWorkPost($post_id)) {
//         $_SESSION['success'] = "Post deleted successfully!";
//     } else {
//         $_SESSION['error'] = "Failed to delete post!";
//     }
//     header("Location: index.php");
//     exit();
// }
?>

<!-- <!DOCTYPE html>
<html lang="en"> -->

<!-- <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head> -->

<!-- <body class="bg-gradient-to-br from-purple-50 to-purple-100 font-inter">

    <div class="max-w-6xl mx-auto p-8"> -->
        <!-- Header -->
        <!-- <header class="text-center mb-10 scale-in">
            <h2 class="text-3xl md:text-4xl font-bold text-indigo-800 mb-3">My Work Posts</h2>
            <p class="text-gray-600 max-w-xl mx-auto">Manage and view all your posted work details in one place. Edit or remove posts as needed.</p>
            <div class="mt-6">
                <a href="post_form.php" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-indigo-700 transition-all btn">
                    <i class="ph ph-plus-circle"></i> Add New Post
                </a>
            </div>
        </header> -->

        <!-- Work Posts Section -->
        <!-- <?php if ($work_posts->num_rows > 0) : ?>
        <div class="grid grid-cols-1 gap-8">
            <?php while ($post = $work_posts->fetch_assoc()) : ?> -->
            <!-- Post Container with Hover Effect -->
            <!-- <div
                class="relative bg-white shadow-lg border-l-8 border-indigo-500 rounded-xl overflow-hidden transform hover:-translate-y-2  hover:shadow-xl transition-all ">
                <div class="p-8"> -->
                    <!-- Post Title -->
                    <!-- <h3 class="text-2xl font-semibold text-indigo-700 mb-6 flex items-center gap-2">
                        <i class="ph ph-briefcase text-indigo-600"></i> Work Details
                    </h3> -->

                    <!-- Post Details -->
                    <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-800">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-user text-indigo-600"></i>
                            <strong>Name:</strong> <?php echo htmlspecialchars($post['name']); ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-phone text-indigo-600"></i>
                            <strong>Mobile:</strong> <?php echo htmlspecialchars($post['mobile']); ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-map-pin text-indigo-600"></i>
                            <strong>City:</strong> <?php echo htmlspecialchars($post['city']); ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-gear text-indigo-600"></i>
                            <strong>Work:</strong> <?php echo htmlspecialchars($post['work']); ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-calendar text-indigo-600"></i>
                            <strong>Deadline:</strong> <?php echo htmlspecialchars($post['deadline']); ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-currency-circle-dollar text-indigo-600"></i>
                            <strong>Work Reward:</strong>
                            <span
                                class="text-green-600 font-bold"><?php echo htmlspecialchars($post['reward']); ?></span>
                        </div>
                        <div class="col-span-2 flex items-start gap-3">
                            <i class="ph ph-chat-circle-dots text-indigo-600"></i>
                            <strong>Message:</strong>
                            <span
                                class="italic text-gray-600">"<?php echo nl2br(htmlspecialchars($post['message'])); ?>"</span>
                        </div>
                        <?php if (!empty($post['from_location']) && !empty($post['to_location'])): ?>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-map-trifold text-blue-600"></i>
                            <strong>From Location:</strong> <?php echo htmlspecialchars($post['from_location']); ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph ph-map-trifold text-red-600"></i>
                            <strong>To Location:</strong> <?php echo htmlspecialchars($post['to_location']); ?>
                        </div>
                        <?php endif; ?>

                    </div> -->

                    <!-- Post Footer -->
                    <!-- <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                        <span class="text-gray-700 text-sm flex items-center gap-2">
                            <i class="ph ph-clock text-indigo-600"></i> Posted on:
                            <?php echo date("d M Y", strtotime($post['created_at'])); ?>
                        </span> -->
                        <!-- Post Footer -->
                    <!-- <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-end items-center gap-4">
                        <div class="flex items-center gap-3">
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>"
                                class="btn bg-indigo-600 text-white font-medium py-2 px-5 rounded-lg hover:bg-indigo-700 transition-all hover:-translate-y-1 flex items-center gap-2 shadow-sm">
                                <i class="ph ph-pencil-simple"></i> Edit
                            </a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                <input type="hidden" name="delete_id" value="<?php echo $post['id']; ?>">
                                <button type="submit"
                                    class="btn bg-red-500 text-white font-medium py-2 px-4 rounded-lg hover:bg-red-700 transition-all hover:-translate-y-1 flex items-center gap-2 shadow-sm">
                                    <i class="ph ph-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else : ?>
        <p class="text-center text-gray-600 text-lg">No work posts available.</p>
        <?php endif; ?>
    </div>

</body>

</html> -->