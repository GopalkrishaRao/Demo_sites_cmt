<?php
require '../shared-components/db.php'; // Database connection
require_once './shared-components/error_reporting.php'; // Error reporting settings
session_start();

require '../components/pin-generator.php'; // Function to generate OTP
require '../Mail/phpmailer/PHPMailerAutoload.php'; // PHPMailer library
require '../components/mailer_function.php'; // Your mailer function

// Prevent session fixation
session_regenerate_id(true);

// For localhost, comment out security headers (uncomment in production)
// header("Content-Security-Policy: default-src 'self'; script-src 'self'");
// header("X-Frame-Options: SAMEORIGIN");
// header("X-Content-Type-Options: nosniff");

$errors = [];
$success_message = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Sanitize and validate email
    $user_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    } else {
        try {
            // Check if the user exists in the `admins` table
            $query = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
            $query->execute(['email' => $user_email]);
            $admin = $query->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Save user email in session
                $_SESSION['reset_email'] = $user_email;

                // Generate OTP
                $otp = generateOtp();
                $otp_expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // Check if a reset request already exists
                $checkResetRequest = $pdo->prepare("SELECT * FROM password_reset_requests WHERE email = :email");
                $checkResetRequest->execute(['email' => $user_email]);
                $existingRequest = $checkResetRequest->fetch(PDO::FETCH_ASSOC);

                if ($existingRequest) {
                    // Update OTP and reset attempts for existing requests
                    $updateQuery = $pdo->prepare("
                        UPDATE password_reset_requests
                        SET otp = :otp, otp_expires_at = :otp_expires_at, otp_reset_attempts = 0, altered_at = CURRENT_TIMESTAMP
                        WHERE email = :email
                    ");
                    $updateQuery->execute([
                        'otp' => $otp,
                        'otp_expires_at' => $otp_expires_at,
                        'email' => $user_email
                    ]);
                } else {
                    // Insert a new reset request
                    $insertQuery = $pdo->prepare("
                        INSERT INTO password_reset_requests (email, otp, otp_expires_at, otp_reset_attempts)
                        VALUES (:email, :otp, :otp_expires_at, 0)
                    ");
                    $insertQuery->execute([
                        'email' => $user_email,
                        'otp' => $otp,
                        'otp_expires_at' => $otp_expires_at
                    ]);
                }

                // TODO: Send OTP via email here
                $success_message = "An OTP has been sent to your email address. Please check your inbox.";

                // Redirect to OTP verification page
                header("Location: varify-otp.php");
                exit();
            } else {
                $errors[] = "No user found with this email address.";
            }
        } catch (PDOException $e) {
            // Log the error and display a user-friendly message
            error_log("Database Error: " . $e->getMessage());
            $errors[] = "An unexpected error occurred. Please try again later.";
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors[] = "Email is required.";
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
                                        <h3>Forgot Password?</h3>
                                        <?php if (!empty($errors)): ?>
                                            <div class="alert alert-danger my-3 text-center">
                                                <p>
                                                    <?php foreach ($errors as $error): ?>
                                                        <?php echo htmlspecialchars($error); ?>
                                                    <?php endforeach; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-login">
                                        <label class="form-label">Registered Email Address</label>
                                        <div class="form-addons">
                                            <input name="email" id="email" class="form-control" placeholder="Enter your registed email" aria-label="Email">

                                        </div>
                                    </div>


                                    <div class="form-login">
                                        <button class="btn btn-login" name="login" type="submit">Send OTP</button>
                                    </div>
                                    <div class="signinform text-center">
                                        <h4>Return to <a href="login.php" class="hover-a"> Login </a></h4>
                                    </div>
                                </div>
                            </form>
                            <div class="my-4 text-center copyright-text">
                                <p>
                                    <?php echo date('Y') ?> &copy; Rajajinagar Education Society. <br>
                                    All rights reserved
                                </p>
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