<?php
session_start();
require_once 'includes/activity_log.php';

// Log logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    $activityLogManager = new ActivityLogManager();
    $activityLogManager->logActivity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
}

// Destroy session
session_destroy();

// Redirect to home page
header('Location: index.php');
exit();
?>
