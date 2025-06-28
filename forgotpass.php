<?php
$login_error = "";
    require('conn.php');
    use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

    function sendMail($email, $reset_token){
        
    require('../phpmailer/PHPMailer.php');
require('../phpmailer/SMTP.php');
require('../phpmailer/Exception.php');
$mail = new PHPMailer(true);

 try {
        //Server settings
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $mail->Username = 'hackzo3152025@gmail.com';                     //SMTP username
        $mail->Password = 'elfz oczh cdoa jlvx ';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('from@example.com', 'HACKZO');
        $mail->addAddress($email);     //Add a recipient

    

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Password Reset Link From HACKZO';
        $mail->Body = 'we got a reset password  request from you clik the link below to reset the password:<br><a href="http://localhost/hackzo/auth/updatepass.php?email='.$email.'&r_token='.$reset_token.'">Reset passsword</a>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
    
    if(isset($_POST['email'])) {
        $email = $_POST['email'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['is_verified'] == 0) {
                $login_error ="<p style='color:red;'>❌ Email not verified. Please check your inbox.</p>";
                exit();
            }
            // Generate a reset token
            $reset_token = bin2hex(random_bytes(16));
            date_default_timezone_set('UTC');
            $date= date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE users SET resettoken  = ? , resettokenexp  = ? WHERE email = ?");
            $stmt->bind_param("sss", $reset_token,$date, $email);
            if ($stmt->execute()&& sendMail($email, $reset_token)) {
                $reset_link = "http://localhost/hackzo/auth/resetpass.php?token=" . $reset_token;
                $login_error ="<p style='color:#00cc66;' class='info'>Reset Link Sent Your Email.</p>";
            } else {
                $login_error = "<p style='color:red;'>❌ Failed to generate reset token.</p>";
            }
        } else {
            $login_error = "<p style='color:red;'>❌ User not found.</p>";
        }
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frogot password | HACKZO</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="forgotpass.css">
    <link rel="icon" href="../imgs/HACKZO-logo-removebg-preview.png" type="image/x-icon">
</head>
<body>
  
    <div class="upper-container">
        <h1>HACKZO</h1>
        <div class="container">  

        <form action="forgotpass.php" method="POST">
            <h2>Forgot Password</h2>
            <div class="error-message">
                <?php if (!empty($login_error)) echo $login_error; ?>   

            </div>
            <label for="email" style="padding-top:20px;">Email Address:</label>
            <input type="text" name="email" id="email" required placeholder="Enter your email">
            
            
            <button type="submit" class="btn">Send Reset Link</button>
            <div class="links">
                    <p>Back To Login<a href="register.php">Login</a></p>                </div>
        </form>
    </div>
    </div>
    </body>
</html>