<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

clearRememberMe();
session_destroy();
header('Location: pages/login.php');
exit();
// Add this JavaScript to clear localStorage on logout
echo '<script>localStorage.removeItem("saved_username"); localStorage.removeItem("saved_password");</script>';
?>