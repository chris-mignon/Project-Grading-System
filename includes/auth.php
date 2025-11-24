<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLecturer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'lecturer';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: ../dashboard.php");
        exit();
    }
}

function redirectIfNotLecturer() {
    redirectIfNotLoggedIn();
    if (!isLecturer()) {
        header("Location: ../dashboard.php");
        exit();
    }
}
?>