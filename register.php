<?php
include 'conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($email, $v_code)
{
    require('../phpmailer/PHPMailer.php');
    require('../phpmailer/SMTP.php');
    require('../phpmailer/Exception.php');

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                                        
        $mail->Host = 'smtp.gmail.com';                    
        $mail->SMTPAuth = true;                                  
        $mail->Username = 'hackzo3152025@gmail.com';     
        $mail->Password = 'elfz oczh cdoa jlvx';                 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
        $mail->Port = 465;                                      
        $mail->setFrom('from@example.com', 'HACKZO');
        $mail->addAddress($email);     
        $mail->isHTML(true);                                  
        $mail->Subject = 'Email Verification - HACKZO';
        $mail->Body = 'Thanks for registering!<br><a href="http://localhost/hackzo/auth/verify.php?email=' . $email . '&v_code=' . $v_code . '">Click to verify</a>';
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phonenumber']);
    $gender = trim($_POST['gender']);
    $dob = $_POST['dob'];
    $password = $_POST['pass'];
    $confirm_password = $_POST['conpass'];

    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate)->y;

    $isStrong = preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);

    if ($age < 13) {
        echo "<p style='color:red; text-align:center;'>❌ Must be at least 13 years old.</p>";
    } elseif ($password !== $confirm_password) {
        echo "<p style='color:red; text-align:center;'>❌ Passwords do not match.</p>";
    } elseif (!$isStrong) {
        echo "<p style='color:red; text-align:center;'>❌ Password must be strong (uppercase, lowercase, number, special char, 8+ chars).</p>";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            echo "<p style='color:red; text-align:center;'>❌ Email already exists.</p>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $v_code = bin2hex(random_bytes(16));
            $is_verified = 0;
            $stmt = $conn->prepare("INSERT INTO users (username, email, phonenumber, dob, gender, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $name, $email, $phone, $dob, $gender, $hashed_password, $v_code, $is_verified);
            if ($stmt->execute()) {
                if (sendMail($email, $v_code)) {
                    echo "<p style='color:green; text-align:center;'>✅ Registered! Check your email for verification.</p>";
                } else {
                    echo "<p style='color:orange; text-align:center;'>⚠️ Registered, but failed to send verification email.</p>";
                }
            } else {
                echo "<p style='color:red; text-align:center;'>❌ Error: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
        $check->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register | HACKZO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="register.css">
  <link rel="icon" href="../imgs/HACKZO-logo-removebg-preview.png">
</head>
<body>
    <div class="container">
        <div class="left-pane">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <form action="register.php" method="POST">
                <h2>Register</h2>
                <div class="form-group">
                    <label for="name">User Name:</label>
                    <input type="text" name="name" id="name" required placeholder="Enter your User name">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required placeholder="Enter your email address">
                </div>
                <div class="form-group">
                    <label for="phonenumber">Phone Number:</label>
                    <input type="text" name="phonenumber" id="phonenumber" required placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" name="dob" id="dob" required>
                    <span id="dobError" style="color:red"></span>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select name="gender" id="gender" required>
                        <option value="" disabled selected>Select your gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pass">Password:</label>
                    <input type="password" name="pass" id="pass" required placeholder="Enter your password">
                </div>
                <div class="form-group">
                    <label for="conpass">Confirm Password:</label>
                    <input type="password" name="conpass" id="conpass" required placeholder="Confirm your password">
                </div>
                <div class="form-group">
                    <label for="showPassword">Show Password</label>
                    <input type="checkbox" id="showPassword" name="Show Password" style="margin-left:-245px;">
                </div>
                <div class="form-group">
                    <button type="submit">Register</button>
                </div>
                <div class="links">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
        <div class="right-pane">
            <h1>HACKZO</h1>
            <h2>Join. Explore. Build.</h2>
            <p>Your journey starts here.</p>
        </div>
    </div>
    <script>
        const showPasswordCheckbox = document.getElementById('showPassword');
        const passwordInput = document.getElementById('pass');
        const confirmPasswordInput = document.getElementById('conpass');
        const dobInput = document.getElementById('dob');
        const dobError = document.getElementById('dobError');

        const today = new Date();
        const minAgeDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
        dobInput.max = minAgeDate.toISOString().split('T')[0];

        document.querySelector("form").addEventListener("submit", function (e) {
            const dobValue = new Date(dobInput.value);
            if (dobValue > minAgeDate) {
                e.preventDefault();
                dobError.textContent = "You must be at least 13 years old to register.";
            } else {
                dobError.textContent = "";
            }
        });

        showPasswordCheckbox.addEventListener('change', function () {
            const type = this.checked ? 'text' : 'password';
            passwordInput.type = type;
            confirmPasswordInput.type = type;
        });
    </script>
</body>
</html>