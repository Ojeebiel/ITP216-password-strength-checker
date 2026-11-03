<?php
// controllers/register-user.php
// Handles the registration request and decides which view to show next.

require_once __DIR__ . '/../model/Model.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/register.php");
    exit();
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if ($name === '' || $email === '' || $password === '') {
    $_SESSION['flash_message'] = "Please fill in all fields.";
    header("Location: ../view/register.php");
    exit();
}

$model  = new Model();
$userId = $model->createUser($name, $email, password_hash($password, PASSWORD_DEFAULT));

if ($userId === false) {
    $_SESSION['flash_message'] = "Error: Email might already be registered.";
    header("Location: ../view/register.php");
    exit();
}

session_regenerate_id(true);
$_SESSION['user_id']   = $userId;
$_SESSION['user_name'] = $name;

header("Location: ../view/dashboard.php");
exit();
