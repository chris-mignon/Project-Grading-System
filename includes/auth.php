<?php
session_start();

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

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

function redirectIfNotAdminOrLecturer() {
    redirectIfNotLoggedIn();
    if (!(isAdmin() || isLecturer())) {
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
