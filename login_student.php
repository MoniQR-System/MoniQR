<?php
session_start();
include 'db_config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $password = $_POST['password']; // In a real app, use password_hash/verify

    if (!empty($student_id) && !empty($password)) {
        // Check if student exists
        $sql = "SELECT * FROM students WHERE student_id = '$student_id' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            // Check password (matching plain text for now as per your setup)
            if ($user_data['password'] === $password) {
                $_SESSION['student_id'] = $user_data['student_id'];
                $_SESSION['student_name'] = $user_data['first_name'] . " " . $user_data['last_name'];
                
                // Redirect to dashboard
                header("Location: student/home.php");
                die;
            } else {
                $error = "Wrong Student ID or Password!";
            }
        } else {
            $error = "Student ID not found!";
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
  <title>MoniQR Portal | Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    body, html { height: 100%; margin: 0; overflow-x: hidden; font-family: 'Poppins', sans-serif; }
    .left-col { width: 60%; background-image: url('img/main.png'); background-size: cover; background-position: center; height: 100vh; }
    .right-col { width: 40%; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; padding: 3rem; text-align: center; background-color: #fff; position: relative;}
    
    .university-logo { width: 100px; margin-bottom: 20px; }
    h1 { font-size: 2rem; font-weight: 500; margin-bottom: 0; }
    .sub-text { font-size: 1.1rem; color: #666; margin-bottom: 30px; }

    .login-form { width: 100%; max-width: 400px; }
    .form-control { background-color: #f0f0f0; border: none; border-radius: 8px; padding: 15px 20px; font-size: 1rem; margin-bottom: 15px; color: #000; transition: 0.3s; }
    .form-control:focus { background-color: #e5e5e5; box-shadow: none; outline: none; }

    .btn-signin { background-color: #800000; color: white; width: 100%; padding: 12px; font-size: 1.1rem; font-weight: 600; border: none; border-radius: 8px; text-transform: uppercase; transition: 0.3s; }
    .btn-signin:hover { background-color: #600000; color: white; transform: translateY(-2px); }

    .alert-custom { font-size: 0.85rem; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
    .forgot-password { display: block; margin-top: 20px; color: #333; text-decoration: none; font-size: 0.9rem; }
    .footer-legal { font-size: 0.7rem; color: #888; position: absolute; bottom: 20px; width: 80%; line-height: 1.4; }

    @media (max-width: 992px) {
      .left-col { display: none; }
      .right-col { width: 100%; }
      .footer-legal { position: relative; margin-top: 40px; }
    }
  </style>
</head>

<body>
  <div class="container-fluid p-0">
    <div class="d-md-flex flex-row">
      <!-- LEFT SIDE IMAGE -->
      <div class="left-col d-none d-md-block"></div>

      <!-- RIGHT SIDE LOGIN -->
      <div class="right-col">
        <img src="img/logo1.png" alt="Logo" class="university-logo">
        <h1>Student Module</h1>
        <p class="sub-text">Please sign in your account!</p>

        <form class="login-form" method="POST" action="login_student.php">
          <!-- ERROR MESSAGE -->
          <?php if($error != ""): ?>
            <div class="alert alert-danger alert-custom"><?php echo $error; ?></div>
          <?php endif; ?>

          <input type="text" name="student_id" class="form-control" placeholder="Student ID:" required>
          <input type="password" name="password" class="form-control" placeholder="Password:" required>
          
          <button type="submit" class="btn btn-signin">Sign In</button>
        </form>

        <a href="#" class="forgot-password">Forgot Password</a>

        <div class="footer-legal">
          By using this service, you understood and agree to the <br>
          PUP Online Services <a href="#">Terms of Use</a> and <a href="#">Privacy Statement</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>