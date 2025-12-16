<?php
// Authentication and session management
session_start();

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function has_role($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Require login (redirect to login page if not logged in)
function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Require specific role
function require_role($role)
{
    require_login();
    if (!has_role($role)) {
        header('Location: index.php');
        exit;
    }
}

// Get current user ID
function get_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function get_user_role()
{
    return $_SESSION['role'] ?? null;
}

// Get current user name
function get_user_name()
{
    return $_SESSION['user_name'] ?? null;
}

// Login user
function login_user($user)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['profile_pic'] = $user['profile_pic'];
}

// Logout user
function logout_user()
{
    session_destroy();
    header('Location: login.php');
    exit;
}

// Sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}
