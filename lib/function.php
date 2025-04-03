<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Uncomment this to ensure session is always available
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
            // First ensure work_posts table exists with correct structure
            $check_table = $this->con->query("SHOW TABLES LIKE 'work_posts'");
            if ($check_table->num_rows === 0) {
                $create_table = "CREATE TABLE IF NOT EXISTS work_posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    mobile VARCHAR(20) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    work VARCHAR(255) NOT NULL,
                    deadline DATE NOT NULL,
                    reward VARCHAR(100) NOT NULL,
                    message TEXT NOT NULL,
                    from_location VARCHAR(255) NULL,
                    to_location VARCHAR(255) NULL,
                    status VARCHAR(20) DEFAULT 'open',
                    assigned_helper_email VARCHAR(255) NULL,
                    deleted TINYINT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if (!$this->con->query($create_table)) {
                    throw new Exception("Failed to create table: " . $this->con->error);
                }
            }

            // Insert the post
            $status = 'open';
            $stmt = $this->con->prepare("INSERT INTO work_posts 
                (name, email, mobile, city, work, deadline, reward, message, from_location, to_location, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->con->error);
            }
            
            $stmt->bind_param("sssssssssss", 
                $name, $email, $mobile, $city, $work, $deadline, $reward, 
                $message, $from_location, $to_location, $status
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $insert_id = $this->con->insert_id;
            $stmt->close();
            
            if ($insert_id) {
                error_log("Successfully inserted post with ID: " . $insert_id);
                return $insert_id;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error in insertWorkPost: " . $e->getMessage());
            throw $e;
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

    // Fetch a single post by ID
    public function getWorkPostById($id) {
        $query = "SELECT * FROM work_posts WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Add this method inside the db_functions class
    public function getWorkPost($post_id) {
        try {
            $query = "SELECT * FROM work_posts WHERE id = ? AND (deleted = 0 OR deleted IS NULL)";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error in getWorkPost: " . $e->getMessage());
            return null;
        }
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
            // Count posts assigned to this helper with pending or pending_approval status
            $query = "SELECT COUNT(*) as count FROM work_posts 
                     WHERE (status = 'pending' OR status = 'pending_approval') 
                     AND assigned_helper_email = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("s", $helper_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['count'];
        } else {
            // Original behavior for admin views
            $query = "SELECT COUNT(*) as count FROM work_posts 
                     WHERE status = 'pending' OR status = 'pending_approval'";
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
        try {
            // Start transaction
            $this->con->begin_transaction();
            
            // Check if post exists and is available
            $check_query = "SELECT status, assigned_helper_email FROM work_posts WHERE id = ? AND deleted = 0";
            $check_stmt = $this->con->prepare($check_query);
            $check_stmt->bind_param("i", $post_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();

            if (!$result) {
                throw new Exception("Post not found");
            }

            if ($result['status'] === 'completed' || $result['status'] === 'accepted') {
                throw new Exception("Post is no longer available");
            }

            if ($result['assigned_helper_email'] === $helper_email) {
                throw new Exception("You have already requested this post");
            }

            // Update post status and assign helper
            $update_query = "UPDATE work_posts 
                            SET assigned_helper_email = ?,
                                status = 'pending_approval',
                                updated_at = NOW()
                            WHERE id = ? 
                            AND (status = 'open' OR status IS NULL OR status = '')
                            AND (assigned_helper_email IS NULL OR assigned_helper_email = '')";
            
            $update_stmt = $this->con->prepare($update_query);
            $update_stmt->bind_param("si", $helper_email, $post_id);
            $success = $update_stmt->execute();
            
            if (!$success || $update_stmt->affected_rows === 0) {
                throw new Exception("Failed to update post status");
            }

            // Commit transaction
            $this->con->commit();
            return true;

        } catch (Exception $e) {
            // Rollback on error
            $this->con->rollback();
            error_log("Error in assignHelper: " . $e->getMessage());
            throw $e;
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
            error_log("Fetching posts for helper: $helper_email");
            
            $current_date = date('Y-m-d');
            $query = "SELECT wp.*, 
                      CASE WHEN LOWER(wp.work) LIKE '%emergency%' THEN 1 ELSE 0 END as is_emergency,
                      CASE WHEN hp.helper_email IS NOT NULL THEN 1 ELSE 0 END as has_pending_request
                      FROM work_posts wp 
                      LEFT JOIN helper_pending_requests hp ON wp.id = hp.post_id AND hp.helper_email = ?
                      WHERE wp.deleted = 0 
                      AND (wp.deadline >= ? OR wp.deadline IS NULL)
                      AND (
                          wp.status = 'open' 
                          OR wp.status IS NULL 
                          OR (wp.status = 'pending' AND hp.helper_email IS NOT NULL)
                      )
                      ORDER BY wp.is_emergency DESC, wp.created_at DESC";

            $stmt = $this->con->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->con->error);
            }

            $stmt->bind_param("ss", $helper_email, $current_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $posts = $result->fetch_all(MYSQLI_ASSOC);
            
            error_log("Found " . count($posts) . " posts for helper");
            return $posts;

        } catch (Exception $e) {
            error_log("Error in getOpenWorkPostsAndHelperRequests: " . $e->getMessage());
            return [];
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

    // Add new function to track pending requests
    public function addHelperPendingRequest($post_id, $helper_email) {
        try {
            $query = "INSERT INTO helper_pending_requests (post_id, helper_email, request_date) 
                      VALUES (?, ?, NOW())";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("is", $post_id, $helper_email);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error adding pending request: " . $e->getMessage());
            return false;
        }
    }
}
?>
