<?php
require('conn.php');

$message = '';
$type = '';

if (isset($_GET['email']) && isset($_GET['v_code'])) {
    $email = $_GET['email'];
    $v_code = $_GET['v_code'];

    $stmt = $conn->prepare("SELECT is_verified FROM users WHERE email = ? AND verification_code = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $email, $v_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['is_verified'] == 1) {
            $message = " Email is already verified. <a href='login.php'>Login</a>";
            $type = 'info';
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
            $update_stmt->bind_param("s", $email);
            if ($update_stmt->execute()) {
                $message = " Email verified successfully! <a href='login.php'>Login</a>";
                $type = 'success';
            } else {
                $message = "Error verifying email.";
                $type = 'error';
            }
        }
    } else {
        $message = "Invalid verification link or code.<br>";
        $type = 'error';

        $debug_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $debug_stmt->bind_param("s", $email);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->get_result();

        if ($debug_result->num_rows === 0) {
            $message .= "Email does not exist in the database.";
        } else {
            $message .= " Email exists, but code doesn't match!";
        }
    }

    $stmt->close();
} else {
    $message = "Missing email or verification code.";
    $type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verify | HACKZO</title>
  <link rel="stylesheet" href="verify.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="../imgs/HACKZO-logo-removebg-preview.png" type="image/x-icon">
</head>
<body>
  <div class="container">
    <div class="message <?= $type ?>">
      <?= $message ?>
    </div>
  </div>
</body>
</html>
