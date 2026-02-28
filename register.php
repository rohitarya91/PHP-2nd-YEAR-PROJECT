<?php
include "db.php";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query(
        $conn,
        "INSERT INTO users(name,email,password)
         VALUES('$name','$email','$pass')"
    );

    header("Location: login.php");
    exit();
}

?>

<!-- <form method="post">
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button name="register">Register</button>
</form> -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up page</title>
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
                <h1>Create Account</h1>
                <p>Start your journey with us</p>
            </div>

            <form class="login-form" id="signupForm" method="post" accept="">
                <div class="input-group">
                    <label for="name">Name</label>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Enter your name" />
                    </div>
                    <span class="error-msg" id="nameError"></span>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" />
                    </div>
                    <span class="error-msg" id="emailError"></span>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" />
                        <button type="button" class="toggle-pw" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-msg" id="passwordError"></span>
                </div>

                <button type="submit" name="register" class="login-btn">
                    <span>Register</span>
                    <i class="fas fa-user-plus"></i>
                </button>

                <div class="divider">
                    <span>or continue with</span>
                </div>

                <div class="social-login">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i>
                    </button>
                    <button type="button" class="social-btn github">
                        <i class="fab fa-github"></i>
                    </button>
                    <button type="button" class="social-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </button>
                </div>

                <p class="signup-link">Already have an account? <a href="./login.php">Login</a></p>
            </form>
        </div>

        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>
    <script src="js/auth.js"></script>
</body>

</html>