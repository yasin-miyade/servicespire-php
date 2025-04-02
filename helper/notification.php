<?php
// session_start();
require_once("../lib/function.php");

if (!isset($_SESSION['helper_email'])) {
    echo "Unauthorized access!";
    exit;
}

$helper_email = $_SESSION['helper_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery for AJAX -->
</head>
<body class="bg-gray-100 font-inter p-6">
    <h2 class="text-2xl font-semibold mb-4">Notifications</h2>

    <ul id="notification-list" class="bg-white shadow-md rounded-lg p-6 space-y-4">
        <p class="text-gray-600">Loading notifications...</p>
    </ul>

    <!-- User Profile Modal - Enhanced Professional Design -->
    <div id="user-profile-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold text-gray-800">User Profile</h3>
                <button onclick="closeUserProfile()" class="text-gray-500 hover:text-gray-800 transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="user-profile-content" class="max-h-[80vh] overflow-y-auto">
                <div class="flex flex-col items-center justify-center p-8 bg-gray-50">
                    <div class="w-16 h-16 mb-4">
                        <div class="animate-spin rounded-full h-full w-full border-t-2 border-b-2 border-indigo-500"></div>
                    </div>
                    <p class="text-gray-500">Loading user profile...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Details Modal -->
    <div id="work-details-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold text-gray-800">Work Details</h3>
                <button onclick="closeWorkDetails()" class="text-gray-500 hover:text-gray-800 transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="work-details-content" class="max-h-[80vh] overflow-y-auto">
                <div class="flex flex-col items-center justify-center p-8 bg-gray-50">
                    <div class="w-16 h-16 mb-4">
                        <div class="animate-spin rounded-full h-full w-full border-t-2 border-b-2 border-indigo-500"></div>
                    </div>
                    <p class="text-gray-500">Loading work details...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fetchNotifications() {
            $.ajax({
                url: "fetch_notifications.php",
                type: "GET",
                success: function (data) {
                    $("#notification-list").html(data);
                }
            });
        }
        
        function handleNotificationAction(postId, action, notificationId) {
            const notificationItem = $(`#notification-${notificationId || postId}`);
            const actionButtons = notificationItem.find('button');
            actionButtons.prop('disabled', true);
            actionButtons.css('opacity', '0.5');
            
            const actionMessage = action === 'accept' ? 'Accepting...' : 'Declining...';
            notificationItem.append(`<div id="action-status-${notificationId || postId}" class="mt-2 text-center text-gray-600">${actionMessage}</div>`);
            
            $.ajax({
                url: "notification_response.php",
                type: "POST",
                data: {
                    post_id: postId,
                    notification_id: notificationId,
                    action: action
                },
                success: function(response) {
                    console.log("Response received:", response);
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.status === 'success') {
                            const statusMsg = action === 'accept' ? 'Accepted! Redirecting...' : 'Declined successfully';
                            $(`#action-status-${notificationId || postId}`).text(statusMsg)
                                .removeClass('text-gray-600')
                                .addClass(action === 'accept' ? 'text-green-600' : 'text-red-600');
                            
                            setTimeout(() => {
                                notificationItem.fadeOut(500);
                                
                                if (action === 'accept') {
                                    setTimeout(() => {
                                        window.location.href = 'index.php?page=pending';
                                    }, 1000);
                                } else {
                                    setTimeout(() => {
                                        notificationItem.remove();
                                    }, 500);
                                }
                            }, 1500);
                        } else {
                            $(`#action-status-${notificationId || postId}`).text(`Error: ${data.message}`)
                                .removeClass('text-gray-600')
                                .addClass('text-red-600');
                            
                            actionButtons.prop('disabled', false);
                            actionButtons.css('opacity', '1');
                        }
                    } catch (e) {
                        console.error("Error parsing response:", e, "Raw response:", response);
                        $(`#action-status-${notificationId || postId}`).text("Error processing response")
                            .removeClass('text-gray-600')
                            .addClass('text-red-600');
                        
                        actionButtons.prop('disabled', false);
                        actionButtons.css('opacity', '1');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error, xhr.responseText);
                    $(`#action-status-${notificationId || postId}`).text(`Error: ${error}`)
                        .removeClass('text-gray-600')
                        .addClass('text-red-600');
                    
                    actionButtons.prop('disabled', false);
                    actionButtons.css('opacity', '1');
                }
            });
        }
        
        function hideNotification(notificationId) {
            console.log("Hiding notification: " + notificationId);
            
            let prefix = '';
            let id = notificationId;
            
            if (typeof notificationId === 'string' && notificationId.startsWith('wp-')) {
                prefix = 'wp-';
                id = notificationId.substring(3);
            }
            
            $("#notification-" + prefix + id).fadeOut(500, function() {
                $(this).hide();
                
                $.ajax({
                    url: "remove_notification.php",
                    type: "POST",
                    data: {
                        notification_id: id,
                        is_work_post: prefix === 'wp-'
                    },
                    success: function(response) {
                        console.log("Notification removal response:", response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error removing notification:", error);
                    }
                });
                
                setTimeout(function() {
                    $("#notification-" + prefix + id).remove();
                    console.log("Notification " + notificationId + " removed after timeout");
                }, 10000);
            });
        }
        
        function showUserProfile(userId) {
            document.getElementById('user-profile-modal').style.display = 'flex';
            
            const modalContent = document.querySelector('#user-profile-modal > div');
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                modalContent.style.transition = 'all 0.3s ease-out';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 50);
            
            $.ajax({
                url: "get_user_profile.php",
                type: "GET",
                data: { user_id: userId },
                success: function(response) {
                    $("#user-profile-content").html(response);
                },
                error: function() {
                    $("#user-profile-content").html(`
                        <div class="p-8 text-center">
                            <div class="inline-flex rounded-full bg-red-100 p-4 mb-4">
                                <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Profile</h3>
                            <p class="text-gray-500">We couldn't load the user profile. Please try again.</p>
                        </div>
                    `);
                }
            });
        }
        
        function closeUserProfile() {
            const modalContent = document.querySelector('#user-profile-modal > div');
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                document.getElementById('user-profile-modal').style.display = 'none';
                $("#user-profile-content").html(`
                    <div class="flex flex-col items-center justify-center p-8 bg-gray-50">
                        <div class="w-16 h-16 mb-4">
                            <div class="animate-spin rounded-full h-full w-full border-t-2 border-b-2 border-indigo-500"></div>
                        </div>
                        <p class="text-gray-500">Loading user profile...</p>
                    </div>
                `);
                modalContent.style.transition = '';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 200);
        }

        function showWorkDetails(postId) {
            document.getElementById('work-details-modal').style.display = 'flex';
            
            const modalContent = document.querySelector('#work-details-modal > div');
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                modalContent.style.transition = 'all 0.3s ease-out';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 50);
            
            $.ajax({
                url: "get_work_details.php",
                type: "GET",
                data: { post_id: postId },
                success: function(response) {
                    $("#work-details-content").html(response);
                },
                error: function() {
                    $("#work-details-content").html(`
                        <div class="p-8 text-center">
                            <div class="inline-flex rounded-full bg-red-100 p-4 mb-4">
                                <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Work Details</h3>
                            <p class="text-gray-500">We couldn't load the work post details. Please try again.</p>
                        </div>
                    `);
                }
            });
        }
        
        function closeWorkDetails() {
            const modalContent = document.querySelector('#work-details-modal > div');
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                document.getElementById('work-details-modal').style.display = 'none';
                $("#work-details-content").html(`
                    <div class="flex flex-col items-center justify-center p-8 bg-gray-50">
                        <div class="w-16 h-16 mb-4">
                            <div class="animate-spin rounded-full h-full w-full border-t-2 border-b-2 border-indigo-500"></div>
                        </div>
                        <p class="text-gray-500">Loading work details...</p>
                    </div>
                `);
                modalContent.style.transition = '';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 200);
        }

        $(document).ready(function () {
            fetchNotifications();
            setInterval(fetchNotifications, 5000);
            
            $('#user-profile-modal, #work-details-modal').click(function(e) {
                if (e.target.id === 'user-profile-modal') {
                    closeUserProfile();
                }
                if (e.target.id === 'work-details-modal') {
                    closeWorkDetails();
                }
            });
        });


        // Add these functions to your existing JavaScript in notification.php

function sendHelpRequest(postId) {
    // Prepare form data
    const formData = new FormData();
    formData.append('help_post_id', postId);
    
    // Show toast notification
    showToast("Sending help request...");
    
    // Send the request
    fetch('help_them_notification.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message || "Request sent successfully!");
            // Refresh notifications after a short delay
            setTimeout(fetchNotifications, 1000);
        } else {
            showToast(data.message || "Failed to send request");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Error sending request: " + error.message);
    });
}

function cancelHelpRequest(postId) {
    // Prepare form data
    const formData = new FormData();
    formData.append('post_id', postId);
    
    // Show toast notification
    showToast("Cancelling request...");
    
    // Send the request
    fetch('cancel_help_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || "Request cancelled successfully!");
            // Refresh notifications after a short delay
            setTimeout(fetchNotifications, 1000);
        } else {
            showToast(data.message || "Failed to cancel request");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast("Error cancelling request: " + error.message);
    });
}

function showToast(message) {
    // Create toast if it doesn't exist
    if (!document.getElementById('toast-notification')) {
        const toast = document.createElement('div');
        toast.id = 'toast-notification';
        toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300 z-50';
        document.body.appendChild(toast);
    }
    const toast = document.getElementById('toast-notification');
    toast.textContent = message;
    toast.classList.remove('opacity-0');
    toast.classList.add('opacity-100');
    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
    }, 3000);
}
    </script>
</body>
</html>
