<?php
/**
 * LIVETVMARKET - Admin Authentication Middleware
 * Checks session and redirects to login if unauthenticated.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
