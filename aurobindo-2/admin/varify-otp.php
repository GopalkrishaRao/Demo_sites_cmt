<?php
require '../shared-components/db.php';
require_once './shared-components/error_reporting.php';
session_start();
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}
// Prevent session fixation
session_regenerate_id(true);

// Security headers (uncomment in production)
// header("Content-Security-Policy: default-src 'self'; script-src 'self'");
// header("X-Frame-Options: SAMEORIGIN");
// header("X-Content-Type-Options: nosniff");

// Initialize errors array
$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Combine OTP digits
    $entered_otp = ($_POST['digit_1'] ?? '') . ($_POST['digit_2'] ?? '') . ($_POST['digit_3'] ?? '') . ($_POST['digit_4'] ?? '');

    // Validate OTP format
    if (!preg_match('/^\d{4}$/', $entered_otp)) {
        $errors[] = "Please enter a valid 4-digit OTP.";
    } else {
        try {
            // Ensure email is in session
            if (!isset($_SESSION['reset_email'])) {
                $errors[] = "No email found in session. Please request a new OTP.";
            } else {
                $email = $_SESSION['reset_email'];

                // Fetch OTP details from the database
                $query = $pdo->prepare("
                    SELECT otp, otp_expires_at, otp_reset_attempts
                    FROM password_reset_requests
                    WHERE email = :email
                ");
                $query->execute(['email' => $email]);
                $resetRequest = $query->fetch(PDO::FETCH_ASSOC);

                if ($resetRequest) {
                    // Check if OTP exists
                    if (empty($resetRequest['otp'])) {
                        $errors[] = "No OTP found. Please request a new one.";
                    } elseif ($resetRequest['otp'] === $entered_otp) {
                        // Check if OTP has expired
                        if (strtotime($resetRequest['otp_expires_at']) < time()) {
                            $errors[] = "OTP has expired. Please request a new one.";
                        } else {
                            // OTP is valid; proceed to generate reset token
                            $reset_token = bin2hex(random_bytes(32)); // Generate secure token
                            $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));


                            // Insert token into password_reset_tokens table
                            $insertTokenQuery = $pdo->prepare("
                                INSERT INTO password_reset_tokens (email, reset_token, reset_token_expiry, created_at, updated_at)
                                VALUES (:email, :reset_token, :reset_token_expiry, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                                ON DUPLICATE KEY UPDATE
                                    reset_token = :reset_token,
                                    reset_token_expiry = :reset_token_expiry,
                                    updated_at = CURRENT_TIMESTAMP
                            ");
                            $insertTokenQuery->execute([
                                'email' => $email,
                                'reset_token' => $reset_token,
                                'reset_token_expiry' => $reset_token_expiry
                            ]);

                            // Reset OTP and attempts in password_reset_requests
                            $updateOtpQuery = $pdo->prepare("
                                UPDATE password_reset_requests
                                SET otp = NULL, otp_reset_attempts = 0, altered_at = CURRENT_TIMESTAMP
                                WHERE email = :email
                            ");
                            $updateOtpQuery->execute(['email' => $email]);

                            // Store token in session and redirect
                            $_SESSION['reset_token'] = $reset_token;
                            $_SESSION['otp_verified'] = true;
                            header("Location: reset-password.php");
                            exit();
                        }
                    } else {
                        // Increment OTP reset attempts
                        $attempts = $resetRequest['otp_reset_attempts'] + 1;

                        $updateAttempts = $pdo->prepare("
                            UPDATE password_reset_requests
                            SET otp_reset_attempts = :attempts, altered_at = CURRENT_TIMESTAMP
                            WHERE email = :email
                        ");
                        $updateAttempts->execute(['attempts' => $attempts, 'email' => $email]);

                        if ($attempts >= 5) {
                            $errors[] = "Too many failed attempts. Please request a new OTP.";
                        } else {
                            $errors[] = "Invalid OTP. Please try again.";
                        }
                    }
                } else {
                    $errors[] = "No OTP request found for this email.";
                }
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
                            <div class="login-userset">
                                <div class="login-userheading text-center">
                                    <h3>OTP Varification</h3>
                                    <h4 class="verfy-mail-content text-center">Enter OTP Received in your Registered Email Address</h4>
                                </div>
                                <form action="" method="POST" class="digit-group">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger my-3 text-center">
                                            <p>
                                                <?php foreach ($errors as $error): ?>
                                                    <?php echo htmlspecialchars($error); ?>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="wallet-add">
                                        <div class="otp-box">
                                            <div class="forms-block text-center">
                                                <input type="password" name="digit_1" id="digit_1" maxlength="1" value="">
                                                <input type="password" name="digit_2" id="digit_2" maxlength="1" value="">
                                                <input type="password" name="digit_3" id="digit_3" maxlength="1" value="">
                                                <input type="password" name="digit_4" id="digit_4" maxlength="1" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="Otp-expire text-center">
                                        <p>Otp will expire in 09 :10</p>
                                    </div>
                                    <div class="form-login mt-4">
                                        <button type="submit" class="btn btn-login">Verify My Account</button>
                                    </div>
                                    <div class="signinform text-center">
                                        <h4>Return to <a href="login.php" class="hover-a"> Login </a></h4>
                                    </div>
                                </form>
                            </div>

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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const digit_1 = document.getElementById('digit_1');
            const digit_2 = document.getElementById('digit_2');
            const digit_3 = document.getElementById('digit_3');
            const digit_4 = document.getElementById('digit_4');

            const pinBoxes = [digit_1, digit_2, digit_3, digit_4];

            // Set focus on the first input box
            digit_1.focus();

            // Function to move focus to the next input box
            function moveToNext(currentBox, nextBox) {
                currentBox.addEventListener('input', () => {
                    if (currentBox.value.length === 1 && nextBox) {
                        nextBox.focus();
                    }
                });
            }

            // Function to move back to the previous input box when backspace is pressed
            function moveToPrevious(currentBox, previousBox) {
                currentBox.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && currentBox.value === '' && previousBox) {
                        previousBox.focus();
                    }
                });
            }

            // Add event listeners to move focus forward and backward
            moveToNext(digit_1, digit_2);
            moveToNext(digit_2, digit_3);
            moveToNext(digit_3, digit_4);

            // Assuming you want to submit when the last box is filled
            moveToNext(digit_4, null); // No next box after last input

            moveToPrevious(digit_2, digit_1);
            moveToPrevious(digit_3, digit_2);
            moveToPrevious(digit_4, digit_3);

            // Clear input fields function
            function clearPinInputs() {
                pinBoxes.forEach(box => box.value = '');
                pinBoxes[0].focus(); // Focus on the first input after clearing
            }
        });
    </script>




</body>

</html>