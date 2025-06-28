<?php
require('conn.php');

$message = ""; // Message shown at top (success or error)

// Check if we have email and token from either GET or POST
if ((isset($_GET['email']) && isset($_GET['r_token'])) || (isset($_POST['email']) && isset($_POST['r_token']))) {
    $email = $_POST['email'] ?? $_GET['email'];
    $reset_token = $_POST['r_token'] ?? $_GET['r_token'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['pass'])) {
            $new_password = $_POST['pass'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ?, resettoken = NULL, resettokenexp = NULL WHERE email = ? AND resettoken = ?");
            $stmt->bind_param("sss", $hashed_password, $email, $reset_token);

            if ($stmt->execute()) {
                $message = "<p class='success'>✅ Password updated successfully</p><a href='login.php' class='a-tag'>Back To Login</a>";
            } else {
                $message = "<p class='error'>❌ Failed to update password.</p>";
            }
            $stmt->close();
        }
    } else {
        // On GET - show form only if token is valid
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND resettoken = ?");
        $stmt->bind_param("ss", $email, $reset_token);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            $message = "<p class='error'>❌ Invalid or expired reset token.</p>";
            $show_form = false;
        } else {
            $show_form = true;
        }
        $stmt->close();
    }
} else {
    $message = "<p class='error'>❌ Missing email or reset token.</p>";
    $show_form = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Update Password | HACKZO</title>
  <link rel="stylesheet" href="updatepass.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="../imgs/HACKZO-logo-removebg-preview.png" type="image/x-icon">
</head>
<body>
  <div class="container">
    <?= $message ? "<div class='message'>$message</div>" : "" ?>

    <?php if (isset($show_form) && $show_form): ?>
      <form action="updatepass.php" method="POST">
        <h2>Update Password</h2>
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="hidden" name="r_token" value="<?= htmlspecialchars($reset_token) ?>">

        <label for="pass">New Password:</label>
        <input type="password" name="pass" id="pass" required>

        <div class="checkbox">
          <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
        </div>

        <button type="submit">Update Password</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    const showPasswordCheckbox = document.getElementById('showPassword');
    const passwordInput = document.getElementById('pass');

    if (showPasswordCheckbox) {
      showPasswordCheckbox.addEventListener('change', function () {
        passwordInput.type = this.checked ? 'text' : 'password';
      });
    }
  </script>
</body>
</html>
