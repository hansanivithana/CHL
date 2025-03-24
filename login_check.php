<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare API data
    $url = 'https://localhost:7150/api/User/Login';
    $data = array(
        "email" => $email,
        "password" => $password
    );

    // cURL initialization
    $ch = curl_init($url);

    // cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Accept: */*"
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Disable SSL verification for local development
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    curl_close($ch);

    // Handle the API response
    if ($response) {
        $json_response = json_decode($response, true);
        if ($json_response['statusCode'] == 200) {
            // Successful login, store user session
            $user = $json_response['data'];
            $userFolder = str_replace(' ', '%20', $user['username']);  // URL encode spaces
            $profilePictureURL = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/$userFolder/" . basename($user['profilePicture']);

            // Store user info in session
            $_SESSION['user'] = [
                'userID' => $user['userID'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'profilePicture' => $profilePictureURL,
                'dateCreated' => $user['dateCreated']
            ];

            $role = $_SESSION['user']['role'];
            
            // Redirect based on role
            switch ($role) {
                case "Admin":
                    header("Location: admin.php");
                    break;
                case "Musician":
                    header("Location: musician.php");
                    break;
                case "Artist":
                    header("Location: artist.php");
                    break;
                case "Instructor":
                    header("Location: instructor.php");
                    break;
                default:
                    echo "<script>alert('Unauthorized role.'); window.location.href = 'loging.php';</script>";
                    exit();
            }
            exit();
        } else {
            // Login failed
            echo "<script>alert('Invalid login credentials. Please try again.'); window.location.href = 'loging.php';</script>";
        }
    } else {
        echo "<script>alert('Error occurred while logging in.'); window.location.href = 'loging.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url("http://localhost/All_FOR_MUSIC/Logo/logo.jpeg") no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: blur(8px);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #FFD700;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }

        .form-control {
            border: 2px solid #FFD700;
            background-color: black;
            color: #FFD700;
        }

        .form-control::placeholder {
            color: #FFD700;
            opacity: 0.8;
        }

        .btn-custom {
            background-color: #FFD700;
            border: none;
            color: black;
            font-size: 16px;
        }

        .btn-custom:hover {
            background-color: darkgoldenrod;
        }

        .forgot-password {
            text-align: right;
            font-size: 14px;
        }

        .forgot-password a {
            color: #FFD700;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .navbar {
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.8);
        }

        .navbar-brand img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">
            <img src="http://localhost/All_FOR_MUSIC/Logo/logo.jpeg" alt="Logo">
        </a>
    </nav>

    <div class="container">
        <h2 class="text-center">Login</h2>
        <form id="loginForm" action="login_check.php" method="POST">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="mb-3 input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <button class="btn btn-outline-warning" type="button" onclick="togglePassword()">👁</button>
            </div>
            <button type="submit" class="btn btn-custom w-100">Login</button>
            <div class="forgot-password mt-2">
                <a href="Register.php">dont have a account?</a>
            </div>
        </form>
    </div>

    <!-- JavaScript for Show Password & Form Validation -->
    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }

        document.getElementById("loginForm").addEventListener("submit", function(event) {
            var email = document.querySelector("[name='email']").value;
            var password = document.querySelector("[name='password']").value;

            if (email.trim() === "" || password.trim() === "") {
                alert("Please fill in all fields.");
                event.preventDefault();
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

