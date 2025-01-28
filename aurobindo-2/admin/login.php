<?php
require '../shared-components/db.php';
require_once './shared-components/error_reporting.php';
session_start();

$errors = [];
$email = $password = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors['password'] = "Please enter a password.";
    }

    if (empty($errors)) {
        try {

            // Fetch user from database
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Store session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['access_level'] = $user['access_level'];
                $_SESSION['institution_id'] = $user['institution_id'];
                header("Location: index.php");
                exit();
            } else {
                if (!$user) {
                    $errors[] = "No user found with that email.";  // Add the error message to the $errors array
                } elseif ($user && !password_verify($password, $user['password'])) {
                    $errors[] = "Invalid password.";
                } else {
                    $errors[] = "Invalid email or password.";
                }
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
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
                                        <h3>Log In</h3>
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
                                        <label class="form-label">Email Address</label>
                                        <div class="form-addons">
                                            <input type="email" class="form-control" id="email" placeholder="Enter your registed email" name="email" aria-label="Email">

                                        </div>
                                    </div>
                                    <div class="form-login">
                                        <label>Password</label>
                                        <div class="pass-group">
                                            <input type="password" class="pass-input form-control" name="password" placeholder="Enter password" aria-label="Password" aria-describedby="password-addon">
                                            <span class="fas toggle-password fa-eye-slash"></span>
                                        </div>
                                    </div>

                                    <div class="form-login">
                                        <button class="btn btn-login" name="login" type="submit">Log In</button>
                                    </div>
                                    <div class="text-center">
                                        <a class="forgot-link" href="forgot-password.php">Forgot Password?</a>
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