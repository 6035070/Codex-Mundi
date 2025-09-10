<?php
require_once 'includes/user.php';

$userManager = new UserManager();
$userManager->logout();

header('Location: index.php');
exit;
?>