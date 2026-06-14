<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_role'] === 'admin') {
    header('Location: admin.php');
} else {
    header('Location: lecturer.php');
}
?>