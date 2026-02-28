<?php
session_start();
include "db.php";
$error[] = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($q);

    if ($user && password_verify($pass, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['email'] = $user['email']; //store email in session 
        if($user['email'] == "admin@gmail.com"){

            header("Location: dashboard.php");
        }else{
            header("location: user_dashbord.php");
        }
        exit();
    } else {
        $error[] = "Invalid_Login";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="login-glass">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-rocket"></i>
                    <span>Harvest Fresh</span>
                </div>
                <h1>Welcome Back</h1>
                <p>Please login to your account</p>
            </div>

            <form class="login-form" id="loginForm" method="post" action="">
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="loginEmail" name="email" placeholder="Enter your email">
                    </div>
                    <span class="error-msg" id="emailError"></span>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="loginPassword" name="password" placeholder="Enter your password">
                        <button type="button" class="toggle-pw" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-msg" id="passwordError">
                        <?php if (in_array("Invalid_Login", $error)) {
                            echo "<small>Invalid password</small>";
                        } ?>
                    </span>
                </div>

                <div class="options">
                    <label class="remember">
                        <input type="checkbox" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-pw">Forgot password?</a>
                </div>

                <button type="submit" name="login" class="login-btn">
                    <span>Login</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <div class="divider">
                    <span>or continue with</span>
                </div>

                <div class="social-login">
                    <button type="button" class="social-btn google">
                        <i class="fa-brands fa-google"></i>
                    </button>
                    <button type="button" class="social-btn github">
                        <i class="fab fa-github"></i>
                    </button>
                    <button type="button" class="social-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </button>
                </div>

                <p class="signup-link">Don't have an account? <a href="./register.php" id="signupLink">Sign up</a></p>
            </form>
        </div>

    </div>

    <script src="js/auth.js"></script>
</body>

</html>