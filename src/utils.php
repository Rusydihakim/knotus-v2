<?php
include 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Random\RandomException;

date_default_timezone_set('Asia/Kuala_Lumpur');

function createUser($conn, $name, $email, $password, $max_budget): array
{
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $max_budget = mysqli_real_escape_string($conn, $max_budget);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $checkQuery = "SELECT id FROM user WHERE email = '$email' LIMIT 1";
    $checkResult = mysqli_query($conn, $checkQuery);
    if (mysqli_num_rows($checkResult) > 0) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    $insertUserQuery = "INSERT INTO user (name, email, password, max_budget) VALUES ('$name', '$email', '$hashedPassword', '$max_budget')";
    if (!mysqli_query($conn, $insertUserQuery)) {
        return ['success' => false, 'message' => 'User creation failed: ' . mysqli_error($conn)];
    }

    $user_id = mysqli_insert_id($conn);

    $copyTasksQuery = "INSERT INTO task (task, `desc`, category, status, user_id)
        SELECT task, `desc`, category, 'Not Started', $user_id FROM preset_task
    ";

    if (!mysqli_query($conn, $copyTasksQuery)) {
        return ['success' => false, 'message' => 'User created, but failed to assign preset tasks: ' . mysqli_error($conn)];
    }

    return ['success' => true, 'message' => 'User registered successfully.'];
}

function loginUser($email, $password): array
{
    global $conn; // Use the $conn from config.php

    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT * FROM user WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        return ['success' => false, 'message' => 'Email not found.'];
    }

    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;

        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Incorrect password.'];
    }
}

/**
 * @throws RandomException
 */
function sendPasswordResetEmail($conn, $email): array
{
    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT id FROM user WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        return ['success' => false, 'message' => 'Email not found.'];
    }

    $user = mysqli_fetch_assoc($result);

    $token = bin2hex(random_bytes(16));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $update = "UPDATE user SET password_reset_token='$token', token_expiry='$expiry' WHERE id=" . intval($user['id']);
    if (!mysqli_query($conn, $update)) {
        return ['success' => false, 'message' => 'Failed to save reset token.'];
    }

    $resetLink = "http://knotus-v2.test/src/pages/reset_password.php?token=$token";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'mail.arica-devs.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'no-reply@arica-devs.com';
        $mail->Password = '}8cy1jJVU_X$';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@arica-devs.com', 'no-reply@arica-devs.com');
        $mail->addAddress($email);
        $mail->isHTML();

        $mail->Subject = 'Password Reset Link';
        $mail->Body = "Click the following link to reset your password:<br><a href='$resetLink'>$resetLink</a>";

        $mail->send();

        return ['success' => true];
    } catch (Exception) {
        return ['success' => false, 'message' => 'Failed to send email: ' . $mail->ErrorInfo];
    }
}

function resetUserPassword($conn, string $token, string $newPassword): array {
    if (empty($token) || empty($newPassword)) {
        return ['success' => false, 'message' => 'Token and new password are required.'];
    }

    $stmt = $conn->prepare("SELECT id, password FROM user WHERE password_reset_token = ? AND token_expiry > NOW() LIMIT 1");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invalid or expired token.'];
    }

    $user = $result->fetch_assoc();

    if (password_verify($newPassword, $user['password'])) {
        return ['success' => false, 'message' => 'New password must be different from the current password.'];
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $conn->prepare("UPDATE user SET password = ?, password_reset_token = NULL, token_expiry = NULL WHERE id = ?");
    if (!$updateStmt) {
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
    $updateStmt->bind_param("si", $hashedPassword, $user['id']);

    if ($updateStmt->execute()) {
        return ['success' => true, 'message' => 'Password has been reset successfully.'];
    } else {
        return ['success' => false, 'message' => 'Failed to reset password. Please try again later.'];
    }
}