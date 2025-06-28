<?php
include 'conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'];
    $password = $_POST['pass'];

    // Detect email or username
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    }

    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check email verification
        if ($user['is_verified'] == 0) {
            echo "<p style='color:red; text-align:center; margin-top:10px;'>❌ Email not verified. Please check your inbox.</p>";
            exit();
        }



        // Password check
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username']; 
            $_SESSION['email'] = $user['email']; 
            header("Location: ../users/hackathons.php");
            exit();
        } else {
            echo "<p style='color:red; text-align:center; margin-top:10px;'>❌ Incorrect password.</p>";
        }
    } else {
        echo "<p style='color:red; text-align:center; margin-top:10px;'>❌ User not found.</p>";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login | HACKZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   <link rel="stylesheet" href="login.css">
   <link rel="icon" href="../imgs/HACKZO-logo-removebg-preview.png" type="image/x-icon">
</head>
<body>
    <div class="container" role="main">
        <div class="left-pane" aria-label="Login form">
            <a href="../static/index.php" class="back-btn">
         <i class="fas fa-arrow-left"></i>
        </a>
            <form action="login.php" method="POST" novalidate>
                <h2>Login</h2>
                <div class="form-group">
                    <label for="identifier">Username or Email:</label>
                    <input type="text" name="identifier" id="identifier" required autocomplete="username" placeholder="Username or Email"/>
                </div>
                <div class="form-group">
                    <label for="pass">Password:</label>
                    <input type="password" name="pass" id="pass" required autocomplete="current-password" placeholder="Password" />
                </div>
                <div class="form-group">
                    <button type="submit" aria-label="Login to your account">Login</button>
                </div>
                <div class="links">
                    <p>I don't have an account <a href="register.php">Register</a></p>
                    <p><a href="forgotpass.php">Forgot Password?</a></p>
                </div>
            </form>
        </div>
        <div class="right-pane" aria-label="About HACKZO">
     <h1>HACKZO</h1>
<h2>Login. Connect. Create.</h2>
<p>Your journey starts here.</p>

        </div>
    </div>
</body>
</html>

