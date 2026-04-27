<?php
session_start();
include 'db_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // We use the 'email' column as the username for login
        $sql = "SELECT * FROM faculty WHERE email = '$username' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $faculty_data = mysqli_fetch_assoc($result);
            
            // Check password
            if ($faculty_data['password'] === $password) {
                // Set session variables
                $_SESSION['faculty_id'] = $faculty_data['id'];
                $_SESSION['faculty_name'] = $faculty_data['name'];
                $_SESSION['faculty_dept'] = $faculty_data['department'];
                
                // Redirect to faculty dashboard
                header("Location: faculty/dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "Username (Email) not found!";
        }
    } else {
        $error = "Please fill in all fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoniQR | Faculty Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    body, html { height: 100%; margin: 0; overflow-x: hidden; font-family: 'Poppins', sans-serif; }
    .left-col { width: 60%; background-image: url('img/main.png'); background-size: cover; background-position: center; height: 100vh; }
    .right-col { width: 40%; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; padding: 3rem; text-align: center; background-color: #fff; position: relative; }
    .university-logo { width: 100px; margin-bottom: 20px; }
    h1 { font-size: 2rem; font-weight: 500; margin-bottom: 0; color: #000; }
    .sub-text { font-size: 1.25rem; color: #333; margin-bottom: 35px; }
    .login-form { width: 100%; max-width: 420px; }
    .form-control { background-color: #dbdbdb; border: none; border-radius: 4px; padding: 16px 20px; font-size: 1.15rem; margin-bottom: 22px; color: #000; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1); }
    .form-control::placeholder { color: #888; opacity: 1; }
    .btn-signin { background-color: #800000; color: white; width: 100%; padding: 14px; font-size: 1.2rem; font-weight: 600; border: none; border-radius: 4px; margin-top: 5px; text-transform: uppercase; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); letter-spacing: 1px; transition: 0.3s; }
    .btn-signin:hover { background-color: #600000; color: white; transform: translateY(-2px); }
    .forgot-password { display: block; margin-top: 25px; color: #333; text-decoration: none; font-size: 0.95rem; }
    .footer-legal { font-size: 0.75rem; color: #444; position: absolute; bottom: 25px; line-height: 1.4; width: 80%; }
    .footer-legal a { color: #0d6efd; text-decoration: none; }

    @media (max-width: 992px) {
      .left-col { display: none; }
      .right-col { width: 100%; }
      .footer-legal { position: relative; margin-top: 50px; }
    }
  </style>
</head>

<body>
  <div class="container-fluid p-0">
    <div class="d-md-flex flex-row">
      <div class="left-col bg-image d-none d-md-block"></div>
      <div class="right-col">
        <img src="img/logo1.png" alt="Logo" class="university-logo">
        <h1>Faculty Module</h1>
        <p class="sub-text">Please sign in your account!</p>

        <!-- Error Alert -->
        <?php if($error != ""): ?>
            <div class="alert alert-danger py-2 w-100 mb-3" style="max-width: 420px; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="login_faculty.php">
          <input type="text" name="username" class="form-control" placeholder="Email / Username:" required>
          <input type="password" name="password" class="form-control" placeholder="Password:" required>
          <button type="submit" class="btn btn-signin">Sign In</button>
        </form>

        <a href="#" class="forgot-password">Forgot Password</a>
        
        <div class="footer-legal">
          By using this service, you understood and <br>
          agree to the PUP Online Services <a href="#">Terms of Use</a> and <a href="#">Privacy Statement</a>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>