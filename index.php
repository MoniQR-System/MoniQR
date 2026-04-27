<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoniQR Portal | Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body, html { height: 100%; margin: 0; overflow-x: hidden; font-family: 'Poppins', sans-serif; }

        .left-col { width: 60%; background-image: url('img/main.png'); background-size: cover; background-position: center; height: 100vh; }
        .right-col { width: 40%; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; padding: 3rem; text-align: center; background-color: #fff; position: relative; }

        .university-logo { width: 100px; margin-bottom: 25px; }
        h1 { font-size: 2.2rem; font-weight: 500; margin-bottom: 5px; color: #1a1a1a; }
        .sub-text { font-size: 1.2rem; color: #555; margin-bottom: 40px; }

        .btn-portal {
            background-color: #800000;
            color: white;
            width: 100%;
            max-width: 350px;
            padding: 16px;
            font-size: 1.2rem;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(128, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .btn-portal:hover {
            background-color: #600000;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
        }

        .footer-legal { font-size: 0.8rem; color: #777; position: absolute; bottom: 30px; line-height: 1.5; width: 80%; }
        .footer-legal a { color: #0d6efd; text-decoration: none; }
        .footer-legal a:hover { text-decoration: underline; }

        @media (max-width: 992px) {
            .left-col { display: none; }
            .right-col { width: 100%; }
            .footer-legal { position: relative; margin-top: 50px; bottom: 0; width: 100%; }
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="d-md-flex flex-row">
            <!-- Left Side Image -->
            <div class="left-col d-none d-md-block"></div>

            <!-- Right Side Selection -->
            <div class="right-col">
                <img src="img/logo1.png" class="university-logo" alt="Logo">
                <h1>Hi Pupian's</h1>
                <p class="sub-text">Please click your destination</p>

                <!-- Navigation Buttons -->
                <a href="login_student.php" class="btn-portal">Student</a>
                <a href="login_faculty.php" class="btn-portal">Faculty</a>

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