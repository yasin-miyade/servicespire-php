<?php
session_start();
require_once("../lib/function.php");

// Verify helper is logged in
if (!isset($_SESSION['helper_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if post_id is provided
if (!isset($_GET['post_id']) || empty($_GET['post_id'])) {
    echo "<div class='p-8 text-center'>
            <div class='bg-red-100 text-red-600 p-4 rounded-lg mb-4'>
                <p>Error: No work post ID provided</p>
            </div>
          </div>";
    exit;
}

$post_id = intval($_GET['post_id']);
$helper_email = $_SESSION['helper_email'];

// Initialize database connection
$db = new db_functions();
$con = $db->connect();

try {
    // Get work post details
    $query = "SELECT * FROM work_posts WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<div class='p-8 text-center'>
                <div class='bg-yellow-100 text-yellow-600 p-4 rounded-lg mb-4'>
                    <p>No work post found with ID: $post_id</p>
                </div>
              </div>";
        exit;
    }
    
    $post = $result->fetch_assoc();
    
    // Determine work post status for UI display
    $status_class = '';
    $status_text = '';
    
    switch ($post['status']) {
        case 'open':
            $status_class = 'bg-green-100 text-green-700';
            $status_text = 'Open';
            break;
        case 'pending':
            $status_class = 'bg-yellow-100 text-yellow-700';
            $status_text = 'Pending';
            break;
        case 'completed':
            $status_class = 'bg-blue-100 text-blue-700';
            $status_text = 'Completed';
            break;
        case 'cancelled':
            $status_class = 'bg-red-100 text-red-700';
            $status_text = 'Cancelled';
            break;
        default:
            $status_class = 'bg-gray-100 text-gray-700';
            $status_text = 'Unknown';
    }
    
    // Format the output with a professional design
    ?>
    
    <div class="p-6">
        <!-- Header with work title and status -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <?php echo htmlspecialchars($post['work']); ?>
            </h2>
            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $status_class; ?>">
                <?php echo $status_text; ?>
            </span>
        </div>
        
        <!-- Work details grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Posted By</h3>
                    <p class="text-gray-900"><?php echo htmlspecialchars($post['name']); ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">City</h3>
                    <p class="text-gray-900"><?php echo htmlspecialchars($post['city']); ?></p>
                </div>
                <?php if (!empty($post['from_location']) && !empty($post['to_location'])): ?>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">From Location</h3>
                    <p class="text-gray-900"><?php echo htmlspecialchars($post['from_location']); ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">To Location</h3>
                    <p class="text-gray-900"><?php echo htmlspecialchars($post['to_location']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Reward</h3>
                    <p class="text-gray-900 font-semibold text-green-600">
                        <?php echo htmlspecialchars($post['reward']); ?>
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Deadline</h3>
                    <p class="text-gray-900">
                        <?php 
                        $deadline_date = new DateTime($post['deadline']);
                        echo $deadline_date->format('F j, Y');
                        
                        // Check if deadline is near (within 3 days)
                        $now = new DateTime();
                        $diff = $now->diff($deadline_date);
                        $days_remaining = $diff->days;
                        
                        if (!$diff->invert && $days_remaining <= 3) {
                            echo ' <span class="text-red-600 font-medium">(';
                            echo $days_remaining == 0 ? 'Today' : ($days_remaining == 1 ? 'Tomorrow' : $days_remaining . ' days left');
                            echo ')</span>';
                        }
                        ?>
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Posted On</h3>
                    <p class="text-gray-900">
                        <?php 
                        $created_date = new DateTime($post['created_at']);
                        echo $created_date->format('F j, Y');
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Message/Description -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Work Description</h3>
            <div class="bg-gray-50 rounded-lg p-4 text-gray-800">
                <?php echo nl2br(htmlspecialchars($post['message'])); ?>
            </div>
        </div>
        
        <!-- Action buttons -->
        <?php if ($post['status'] == 'open'): ?>
        <div class="flex justify-end gap-4 mt-6">
            <button onclick="closeWorkDetails()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Close
            </button>
            
            <?php if ($post['assigned_helper_email'] == $helper_email): ?>
            <button onclick="cancelHelpRequest(<?php echo $post_id; ?>); closeWorkDetails();" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Cancel Request
            </button>
            <?php else: ?>
            <button onclick="sendHelpRequest(<?php echo $post_id; ?>); closeWorkDetails();" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Help With This
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="flex justify-end gap-4 mt-6">
            <button onclick="closeWorkDetails()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Close
            </button>
        </div>
        <?php endif; ?>
    </div>
    
<?php
} catch (Exception $e) {
    echo "<div class='p-8 text-center'>
            <div class='bg-red-100 text-red-600 p-4 rounded-lg mb-4'>
                <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
            </div>
          </div>";
}
?>
