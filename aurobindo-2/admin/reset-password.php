<?php
require '../shared-components/db.php';
require_once './shared-components/error_reporting.php';
session_start();

// Prevent session fixation
session_regenerate_id(true);

// Initialize errors array
$errors = [];

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_token'])) {
    header("Location: forgot-password.php");
    exit();
}
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $reset_token = $_SESSION['reset_token'] ?? '';
    $reset_email = $_SESSION['reset_email'] ?? '';

    // Validate the new password
    if (empty($new_password) || empty($confirm_password)) {
        $errors[] = "Please enter and confirm your new password.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // Validate the reset token and email
    if (empty($reset_token) || empty($reset_email)) {
        $errors[] = "Invalid or missing token. Please try again.";
    }

    if (empty($errors)) {
        try {
            // Fetch reset token and expiry details from the database
            $query = $pdo->prepare("
                SELECT * 
                FROM password_reset_tokens 
                WHERE email = :email
                ORDER BY id DESC 
                LIMIT 1
            ");
            $query->execute(['email' => $reset_email]);
            $resetRequest = $query->fetch(PDO::FETCH_ASSOC);

            if (!$resetRequest) {
                $errors[] = "No reset request found for this email.";
            } elseif ($resetRequest['reset_token'] !== $reset_token) {
                $errors[] = "Invalid reset token. Please try again.";
            } elseif (strtotime($resetRequest['reset_token_expiry']) < time()) {
                $errors[] = "Reset token has expired. Please request a new one.";
            }

            // If no errors, update the password
            if (empty($errors)) {
                // Hash the new password securely
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update the user's password in the 'admins' table
                $updatePassword = $pdo->prepare("
                    UPDATE admins 
                    SET password = :password 
                    WHERE email = :email
                ");
                $updatePassword->execute([
                    'password' => $hashed_password,
                    'email' => $reset_email
                ]);

                // Delete the latest reset request for the given email from the 'password_reset_tokens' table
                $deleteResetRequest = $pdo->prepare("
    DELETE FROM password_reset_tokens 
    WHERE email = :email
");
                $deleteResetRequest->execute(['email' => $reset_email]);


                // Set OTP to NULL in the 'password_reset_requests' table
                // $nullifyOtp = $pdo->prepare("
                //     UPDATE password_reset_requests 
                //     SET otp = NULL 
                //     WHERE email = :email
                // ");
                // $nullifyOtp->execute(['email' => $reset_email]);

                // Destroy all session data related to the password reset
                unset($_SESSION['reset_email'], $_SESSION['reset_token'], $_SESSION['otp_verified']);
                session_write_close();

                // Redirect to login page with success message
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $errors[] = "An unexpected error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require './shared-components/head.php'; ?>
</head>

<body>
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="container">
                    <div class="login-content user-login">
                        <div class="login-logo">
                            <img src="assets/img/logo.png" alt="img">
                            <a href="#" class="login-logo logo-white">
                                <img src="assets/img/logo-white.png" alt="">
                            </a>
                        </div>
                        <div class="col-md-4">
                            <form action="" method="POST">
                                <div class="login-userset">
                                    <div class="login-userheading">
                                        <h3>Reset password?</h3>
                                        <h4>Enter New Password & Confirm Password to get inside</h4>
                                    </div>
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger my-3 text-center">
                                            <p>
                                                <?php foreach ($errors as $error): ?>
                                                    <?php echo htmlspecialchars($error); ?>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-login">
                                        <label>New Password</label>
                                        <div class="pass-group">
                                            <input type="password" name="new_password" class="pass-input" required>
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>

                                    <div class="form-login">
                                        <label>Confirm Password</label>
                                        <div class="pass-group">
                                            <input type="password" name="confirm_password" class="pass-input" required>
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <button type="submit" class="btn btn-login">Change Password</button>
                                    </div>
                                    <div class="signinform text-center">
                                        <h4>Return to <a href="login.php" class="hover-a"> Login </a></h4>
                                    </div>
                                </div>
                            </form>


                            <div class="my-4 text-center copyright-text">
                                <p> <?php echo date('Y') ?> &copy; Rajajinagar Education Society. <br>
                                    All rights reserved</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php require './shared-components/scripts.php'; ?>

</body>

</html>