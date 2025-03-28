<?php
if (session_status() === PHP_SESSION_NONE) {
    // session_start();
}

date_default_timezone_set('Asia/Kolkata');

// Add connectDB function for backward compatibility
function connectDB() {
    $db = new db_functions();
    return $db->connect();
}

class db_functions 
{
    private $con;

    function __construct()
    {
        $this->con = new mysqli("localhost", "root", "", "servicespire");
        if ($this->con->connect_error) {
            die("Connection failed: " . $this->con->connect_error);
        }
    }

    public function connect() {
        return $this->con;
    }

    // Add missing get_connection method
    public function get_connection() {
        return $this->con;
    }

    //contact data function
    public function save_contact_data($username, $email, $phone, $message) {
        $date = date("Y-m-d");
        $time = date("H:i:s");

        $stmt = $this->con->prepare("INSERT INTO contact_data (username, email, phone, message, date, time) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Error preparing statement: " . $this->con->error);
        }

        $stmt->bind_param("ssssss", $username, $email, $phone, $message, $date, $time);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    // user sign up function
    public function save_sign_up_data($first_name, $last_name, $dob, $gender, $mobile, $address, $email, $password, $id_proof_path) {
        // Validate password requirements (8+ chars, upper, lower, number, symbol)
        if (!$this->validatePassword($password)) {
            error_log("Password failed validation requirements");
            return false;
        }
        
        // Store password as plain text (not recommended for production)
        $query = "INSERT INTO sign_up (first_name, last_name, dob, gender, mobile, address, email, password, id_proof) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->con->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->con->error);
            return false;
        }
        
        $stmt->bind_param("sssssssss", $first_name, $last_name, $dob, $gender, $mobile, $address, $email, $password, $id_proof_path);
        
        if ($stmt->execute()) {
            return true; // Data saved successfully
        } else {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
    }

    // Add password validation function
    public function validatePassword($password) {
        // Check for minimum length
        if (strlen($password) < 8) {
            return false;
        }
        
        // Check for uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for lowercase
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for numbers
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for special characters
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }

    //user login function
    public function get_user_by_email($email) {
        $query = "SELECT * FROM sign_up WHERE email = ?";
        $stmt = $this->con->prepare($query);
    
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->con->error);
            return null; // Return null to indicate failure
        }
    
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc(); // Return user data
        } else {
            return null; // No user found
        }
    }

    // New function to authenticate user
    public function authenticate_user($email, $password) {
        $user = $this->get_user_by_email($email);
        if ($user === null) {
            return false; // User not found
        }
        
        // Direct password comparison (no hashing)
        if ($password === $user['password']) {
            return $user; // Authentication successful, return user data
        } else {
            return false; // Password doesn't match
        }
    }

    //user post form data storing
    public function insertWorkPost($name, $email, $mobile, $city, $work, $deadline, $reward, $message, $from_location, $to_location) {
        try {
            // Set default status to 'open' for new posts
            $status = 'open';
            
            // Check first if the work_posts table exists
            $check_table = $this->con->query("SHOW TABLES LIKE 'work_posts'");
            if ($check_table->num_rows === 0) {
                // Table doesn't exist, create it
                $create_table_sql = "CREATE TABLE work_posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    mobile VARCHAR(20) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    work VARCHAR(255) NOT NULL,
                    deadline DATE NOT NULL,
                    reward VARCHAR(100) NOT NULL,
                    message TEXT NOT NULL,
                    from_location VARCHAR(255),
                    to_location VARCHAR(255),
                    status VARCHAR(20) DEFAULT 'open',
                    assigned_helper_email VARCHAR(255) NULL,
                    notification TEXT NULL,
                    deleted TINYINT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
                $this->con->query($create_table_sql);
            }
            
            // Check if status column exists
            $check_column = $this->con->query("SHOW COLUMNS FROM work_posts LIKE 'status'");
            if ($check_column->num_rows === 0) {
                // Add status column if it doesn't exist
                $this->con->query("ALTER TABLE work_posts ADD COLUMN status VARCHAR(20) DEFAULT 'open'");
            }
            
            // Now insert with the status
            $stmt = $this->con->prepare("INSERT INTO work_posts (name, email, mobile, city, work, deadline, reward, message, from_location, to_location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                error_log("Error preparing statement: " . $this->con->error);
                return false;
            }
            
            $stmt->bind_param("sssssssssss", $name, $email, $mobile, $city, $work, $deadline, $reward, $message, $from_location, $to_location, $status);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error executing statement: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Exception in insertWorkPost: " . $e->getMessage());
            return false;
        }
    }
    
	
    //show the users post at helper dashboard
    public function getWorkPosts() {
        $query = "SELECT * FROM work_posts WHERE deleted = 0 ORDER BY id DESC";
        $result = $this->con->query($query);

        if (!$result) {
            die("Query Failed: " . $this->con->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Add new function to get available work posts for a specific helper
    public function getAvailableWorkPosts($helper_email) {
        $query = "SELECT * FROM work_posts WHERE deleted = 0 AND (assigned_helper_email IS NULL OR assigned_helper_email = ?) ORDER BY id DESC";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $helper_email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //users own posts 
    public function getUserWorkPosts($email) {
        $stmt = $this->con->prepare("SELECT * FROM work_posts WHERE email = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Fetch a single post by ID
    function getUserWorkPostById($post_id, $email) {
        $stmt = $this->con->prepare("SELECT * FROM work_posts WHERE id = ? AND email = ?");
        $stmt->bind_param("is", $post_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update a user's post
    function updateUserWorkPost($post_id, $email, $work, $city, $deadline, $reward, $message, $from_location, $to_location) {
        $stmt = $this->con->prepare("UPDATE work_posts SET work = ?, city = ?, deadline = ?, reward = ?, from_location = ?, to_location = ?, `message` = ? WHERE id = ? AND email = ?");
        $stmt->bind_param("sssssssss", $work, $city, $deadline, $reward, $from_location, $to_location, $message, $post_id, $email);
        return $stmt->execute();
    }

    // Delete a user's post
    public function deleteWorkPost($post_id) {
        $con = $this->connect();
        $stmt = $con->prepare("DELETE FROM work_posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    ///// helper dashboard working
    public function getTotalRequests($helper_email = '', $current_date = null) {
        try {
            // Start with basic query to count only open posts
            $query = "SELECT COUNT(*) as count FROM work_posts WHERE 1=1";
            
            // Add deadline filter if current date is provided
            if ($current_date !== null) {
                $query .= " AND (deadline >= ? OR deadline IS NULL OR deadline = '')";
            }
            
            // Only include posts with 'open' status or null/empty status
            // This specifically excludes pending and completed posts
            $query .= " AND (status = 'open' OR status IS NULL OR status = '')";
            
            // Exclude posts assigned to this helper
            if (!empty($helper_email)) {
                $query .= " AND (assigned_helper_email IS NULL OR assigned_helper_email != ?)";
            }
            
            $stmt = $this->con->prepare($query);
            
            // Bind parameters based on what was included in query
            if ($current_date !== null && !empty($helper_email)) {
                $stmt->bind_param("ss", $current_date, $helper_email);
            } else if ($current_date !== null) {
                $stmt->bind_param("s", $current_date);
            } else if (!empty($helper_email)) {
                $stmt->bind_param("s", $helper_email);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'];
        } catch (Exception $e) {
            error_log("Error in getTotalRequests: " . $e->getMessage());
            return 0;
        }
    }

    // Update to filter by helper email
    public function getPendingRequests($helper_email = null) {
        if ($helper_email) {
            // Count only posts assigned to this helper with pending status
            $query = "SELECT COUNT(*) as count FROM work_posts WHERE status = 'pending' AND assigned_helper_email = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("s", $helper_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'];
        } else {
            // Original behavior for admin views
            $query = "SELECT COUNT(*) as count FROM work_posts WHERE status = 'pending'";
            $result = $this->con->query($query);
            $row = $result->fetch_assoc();
            return $row['count'];
        }
    }

    // Update to filter by helper email
    public function getCompletedTasks($helper_email = null) {
        if ($helper_email) {
            // Count only posts assigned to this helper with completed status
            $query = "SELECT COUNT(*) as count FROM work_posts WHERE status = 'completed' AND assigned_helper_email = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("s", $helper_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'];
        } else {
            // Original behavior for admin views
            $query = "SELECT COUNT(*) as count FROM work_posts WHERE status = 'completed'";
            $result = $this->con->query($query);
            $row = $result->fetch_assoc();
            return $row['count'];
        }
    }

    public function removePendingRequests($post_id) {
        $query = "UPDATE work_posts SET status = 'cancelled' WHERE id = ? AND status = 'pending'";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $post_id);
        
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0; // Returns true if a row was updated
        } else {
            // Handle potential errors
            return false;
        }
    }

    // Add this new function to unassign a helper
    public function unassignHelper($post_id) {
        $query = "UPDATE work_posts SET assigned_helper_email = NULL WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $post_id);
        return $stmt->execute();
    }

    public function assignHelper($post_id, $helper_email) {
        // Check if this helper was previously assigned to this post
        $check_query = "SELECT assigned_helper_email, status FROM work_posts WHERE id = ?";
        $check_stmt = $this->con->prepare($check_query);
        $check_stmt->bind_param("i", $post_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Get current timestamp in correct format
        $current_time = date("Y-m-d H:i:s");
        
        // If this helper was previously assigned and is requesting again, update the timestamp
        if ($row && $row['assigned_helper_email'] === $helper_email) {
            // If already assigned but not accepted yet (status is still 'open'), just update timestamp
            $update_query = "UPDATE work_posts SET updated_at = ? WHERE id = ?";
            $update_stmt = $this->con->prepare($update_query);
            $update_stmt->bind_param("si", $current_time, $post_id);
            return $update_stmt->execute();
        } else {
            // New assignment - set assigned_helper_email but keep status as 'open'
            // This will make it appear in notifications for the user to accept
            $query = "UPDATE work_posts SET assigned_helper_email = ?, updated_at = ? WHERE id = ? AND (assigned_helper_email IS NULL OR status = 'open')";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("ssi", $helper_email, $current_time, $post_id);
            
            $result = $stmt->execute();
            $affected = $stmt->affected_rows;
            
            // Also create a notification for the user
            if ($result && $affected > 0) {
                // Insert into notifications table if it exists
                $check_table = $this->con->query("SHOW TABLES LIKE 'notifications'");
                if ($check_table->num_rows > 0) {
                    // Get user email from post
                    $user_query = "SELECT email FROM work_posts WHERE id = ?";
                    $user_stmt = $this->con->prepare($user_query);
                    $user_stmt->bind_param("i", $post_id);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    $user_data = $user_result->fetch_assoc();
                    
                    if ($user_data) {
                        // Create notification
                        $notify_query = "INSERT INTO notifications (user_email, message, post_id, created_at) 
                                         VALUES (?, 'A helper has requested to help with your work', ?, NOW())";
                        $notify_stmt = $this->con->prepare($notify_query);
                        $notify_stmt->bind_param("si", $user_data['email'], $post_id);
                        $notify_stmt->execute();
                    }
                }
            }
            
            return $result;
        }
    }

    public function register_user($first_name, $last_name, $email, $mobile, $gender, $dob, $address, $password, $id_proof, $id_proof_file)
    {
        $stmt = $this->con->prepare("INSERT INTO helper_sign_up (first_name, last_name, email, mobile, gender, dob, address, password, id_proof, id_proof_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $mobile, $gender, $dob, $address, $password, $id_proof, $id_proof_file);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function login_user($email, $password)
    {
        // Start the session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $stmt = $this->con->prepare("SELECT id, first_name, last_name, email, password FROM helper_sign_up WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Check if user exists
        if (!$result) {
            error_log("No user found with email: $email");
            return false;
        }

        // Direct string comparison
        if ($password === $result['password']) {
            // Store user data in session
            $_SESSION['helper_id'] = $result['id'];
            $_SESSION['helper_email'] = $result['email'];
            $_SESSION['helper_first_name'] = $result['first_name'];
            $_SESSION['helper_last_name'] = $result['last_name'];
            
            return $result;
        } else {
            error_log("Password mismatch for email: $email");
            return false;
        }
    }

    public function is_email_exists($email) {
        $query = "SELECT id FROM helper_sign_up WHERE email = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        return $stmt->num_rows > 0;
    }

    public function isHelperAssigned($post_id, $helper_email) {
        $query = "SELECT assigned_helper_email FROM work_posts WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return ($result['assigned_helper_email'] === $helper_email);
    }

    public function getUserByPostId($post_id) {
        $query = "SELECT email FROM work_posts WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function sendNotification($post_id, $message) {
        $query = "UPDATE work_posts SET notification = ? WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("si", $message, $post_id);
        return $stmt->execute();
    }

    public function getUserNotifications($user_email) {
        $query = "SELECT notification FROM work_posts WHERE email = ? AND notification IS NOT NULL";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //admin dashboard working
    public function getHelpers() {
        $query = "SELECT * FROM helper_sign_up"; // Use the correct table name
        $result = $this->con->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUsers() {
        if (!$this->con) {
            return []; // Return an empty array if DB connection fails
        }

        $query = "SELECT * FROM sign_up"; // Ensure table name is correct
        $stmt = $this->con->prepare($query);
        
        if (!$stmt) {
            error_log("DB Error: " . $this->con->error); // Log error for debugging
            return [];
        }
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $users = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $users;
        } else {
            error_log("Query Execution Error: " . $stmt->error);
            return [];
        }
    }

    public function getUserById($id) {
        if (!$this->con) {
            return null; // Return null if DB connection fails
        }

        $stmt = $this->con->prepare("SELECT * FROM sign_up WHERE id = ?");
        
        if (!$stmt) {
            error_log("DB Error: " . $this->con->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("Query Execution Error: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user ?: null; // Return null if no user is found
    }

    ///profile autofill of user
    public function get_user_by_id($user_id) {
        $query = "SELECT * FROM sign_up WHERE id = ?";
        $stmt = $this->con->prepare($query);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->con->error);
            return null;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function update_user_profile($user_id, $first_name, $last_name, $dob, $gender, $mobile, $address, $bio, $profile_photo = null) {
        // If bio column doesn't exist, you may need to alter the table first
        // ALTER TABLE sign_up ADD COLUMN bio TEXT;
        // ALTER TABLE sign_up ADD COLUMN profile_photo VARCHAR(255);
        
        $query = "UPDATE sign_up SET 
                  first_name = ?, 
                  last_name = ?, 
                  dob = ?, 
                  gender = ?, 
                  mobile = ?, 
                  address = ?, 
                  bio = ?";
                  
        $params = [$first_name, $last_name, $dob, $gender, $mobile, $address, $bio];
        $types = "sssssss";
        
        // Add profile photo to update if provided
        if ($profile_photo !== null) {
            $query .= ", profile_photo = ?";
            $params[] = $profile_photo;
            $types .= "s";
        }
        
        $query .= " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";
        
        $stmt = $this->con->prepare($query);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->con->error);
            return false;
        }
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
    }

    public function delete_user_account($user_id) {
        $query = "DELETE FROM sign_up WHERE id = ?";
        $stmt = $this->con->prepare($query);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->con->error);
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
    }

    // Forgot password User
    public function update_user_password($email, $new_password) {
        $stmt = $this->con->prepare("UPDATE sign_up SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);
        return $stmt->execute();
    }

    // Forgot password helper
    public function update_helper_password($email, $new_password) {
        $stmt = $this->con->prepare("UPDATE helper_sign_up SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);
        return $stmt->execute();
    }

    public function get_helper_by_email($email) {
        $stmt = $this->con->prepare("SELECT * FROM helper_sign_up WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    //profile autofill of helper
    public function get_helper_id($helper_id) {
        $query = "SELECT * FROM helper_sign_up WHERE id = ?";
        $stmt = $this->con->prepare($query);
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->con->error);
            return null;
        }
        
        $stmt->bind_param("i", $helper_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    // Add missing update_helper_profile method for backward compatibility
    public function update_helper_profile($helper_id, $first_name, $last_name, $mobile, $gender, $dob, $address, $bio, $profile_photo_path) {
        // This is a wrapper for the existing updateHelperProfile method
        return $this->updateHelperProfile($helper_id, $first_name, $last_name, $dob, $gender, $mobile, $address, $bio, $profile_photo_path);
    }

    // Function to update helper profile
    function updateHelperProfile($helper_id, $first_name, $last_name, $dob, $gender, $mobile, $address, $bio, $profile_photo_path) {
        $update_sql = "UPDATE helper_sign_up SET 
                    first_name = ?, 
                    last_name = ?, 
                    mobile = ?, 
                    gender = ?, 
                    dob = ?, 
                    address = ?, 
                    bio = ?,
                    profile_photo = ? 
                    WHERE id = ?";
                    
        $stmt = $this->con->prepare($update_sql);
        $stmt->bind_param("ssssssssi", 
            $first_name, 
            $last_name, 
            $mobile, 
            $gender, 
            $dob, 
            $address, 
            $bio,
            $profile_photo_path,
            $helper_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // Fix the deleteHelperAccount function to use $this->con instead of get_connection
    function deleteHelperAccount($helper_id) {
        // First get the helper email so we can remove their assignments
        $email_query = "SELECT email FROM helper_sign_up WHERE id = ?";
        $email_stmt = $this->con->prepare($email_query);
        $email_stmt->bind_param("i", $helper_id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        
        if ($email_result->num_rows > 0) {
            $helper_data = $email_result->fetch_assoc();
            $helper_email = $helper_data['email'];
            
            // Reset all work posts assigned to this helper
            $reset_query = "UPDATE work_posts SET assigned_helper_email = NULL, status = 'open' 
                           WHERE assigned_helper_email = ?";
            $reset_stmt = $this->con->prepare($reset_query);
            $reset_stmt->bind_param("s", $helper_email);
            $reset_stmt->execute();
            $reset_stmt->close();
            
            // Clean up from notifications table if it exists
            $check_table = $this->con->query("SHOW TABLES LIKE 'notifications'");
            if ($check_table->num_rows > 0) {
                // Delete notifications from this helper
                $notif_query = "DELETE FROM notifications WHERE sender_id = ?";
                $notif_stmt = $this->con->prepare($notif_query);
                $notif_stmt->bind_param("i", $helper_id);
                $notif_stmt->execute();
                $notif_stmt->close();
            }
        }
        $email_stmt->close();
        
        // Now delete the helper account
        $sql = "DELETE FROM helper_sign_up WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $helper_id);
        $result = $stmt->execute();
        
        $stmt->close();
        
        return $result;
    }

    public function getOpenWorkPostsAndHelperRequests($helper_email) {
        try {
            // Debug the function call
            error_log("Getting open work posts for helper: $helper_email");
            
            // Get current date for deadline check
            $current_date = date('Y-m-d');
            
            // Check if status field exists in the work_posts table
            $check_status_field = $this->con->query("SHOW COLUMNS FROM `work_posts` LIKE 'status'");
            
            if ($check_status_field->num_rows === 0) {
                // No status field, use simpler query focusing on the assigned_helper_email field
                $query = "SELECT * FROM work_posts 
                        WHERE (deleted = 0 OR deleted IS NULL)
                        AND (deadline >= ? OR deadline IS NULL) 
                        AND (
                            assigned_helper_email IS NULL 
                            OR assigned_helper_email = ?
                        )
                        ORDER BY id DESC";
                        
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("ss", $current_date, $helper_email);
            } else {
                // Modified query to correctly fetch posts that should be available to helpers
                // This includes posts with NULL or empty status and unassigned posts
                $query = "SELECT * FROM work_posts 
                        WHERE (deleted = 0 OR deleted IS NULL)
                        AND (deadline >= ? OR deadline IS NULL)
                        AND (
                            (status = 'open' OR status IS NULL OR status = '') 
                            OR (assigned_helper_email IS NULL AND status != 'completed' AND status != 'pending_approval' AND status != 'accepted')
                            OR (assigned_helper_email = ? AND status != 'completed')
                        )
                        ORDER BY id DESC";
                        
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("ss", $current_date, $helper_email);
            }
            
            if (!$stmt) {
                error_log("Error preparing statement: " . $this->con->error);
                return array(); // Return empty array on error
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                error_log("Error executing query: " . $stmt->error);
                return array();
            }
            
            $posts = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            error_log("Found " . count($posts) . " available posts for helper: $helper_email");
            
            return $posts;
        } catch (Exception $e) {
            error_log("Exception in getOpenWorkPostsAndHelperRequests: " . $e->getMessage());
            return array();
        }
    }

    // Add a function to fix post status values
    public function fixPostsStatus() {
        try {
            // Find posts with NULL or empty status that are unassigned and should be open
            $query = "UPDATE work_posts 
                      SET status = 'open' 
                      WHERE (status IS NULL OR status = '') 
                      AND (assigned_helper_email IS NULL OR assigned_helper_email = '')
                      AND deleted = 0";
            
            $stmt = $this->con->prepare($query);
            if (!$stmt) {
                error_log("Error preparing status fix statement: " . $this->con->error);
                return false;
            }
            
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            error_log("Fixed status for $affected posts");
            return true;
        } catch (Exception $e) {
            error_log("Exception in fixPostsStatus: " . $e->getMessage());
            return false;
        }
    }
    
    // Add a new debug function to help troubleshoot post visibility issues
    public function debugWorkPosts($helper_email) {
        $debug = [];
        
        // Check if work_posts table exists
        $tables = $this->con->query("SHOW TABLES LIKE 'work_posts'")->num_rows;
        $debug['work_posts_table_exists'] = $tables > 0 ? 'Yes' : 'No';
        
        // Count all posts
        $all_posts = $this->con->query("SELECT COUNT(*) as count FROM work_posts")->fetch_assoc()['count'];
        $debug['total_posts'] = $all_posts;
        
        // Count posts with open status
        $open_posts = $this->con->query("SELECT COUNT(*) as count FROM work_posts WHERE status = 'open' OR status IS NULL")->fetch_assoc()['count'];
        $debug['open_posts'] = $open_posts;
        
        // Count posts with null helper assignment
        $unassigned = $this->con->query("SELECT COUNT(*) as count FROM work_posts WHERE assigned_helper_email IS NULL")->fetch_assoc()['count'];
        $debug['unassigned_posts'] = $unassigned;
        
        // Count posts with this helper assigned
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM work_posts WHERE assigned_helper_email = ?");
        $stmt->bind_param("s", $helper_email);
        $stmt->execute();
        $assigned = $stmt->get_result()->fetch_assoc()['count'];
        $debug['posts_assigned_to_helper'] = $assigned;
        
        return $debug;
    }
}
?>
