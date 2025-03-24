<?php
// Check if the role is passed via the URL, and set it to Admin by default
$role = isset($_GET['role']) ? $_GET['role'] : 'User';  // Default to 'User' if not passed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving form data
    $username = trim($_POST['username']); // Trim spaces
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Validate required fields
    if (empty($username) || empty($password) || empty($retype_password) || empty($email) || empty($role)) {
        echo "<script>alert('Please fill all required fields!');</script>";
        exit();
    }

    // Validate passwords
    if ($password !== $retype_password) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!');</script>";
        exit();
    }

    // Hashing the password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

  // Define Upload Directory
$upload_dir = __DIR__ . "/Uploads/Profile_Picture/" . $username . "/";

// Ensure Directory Exists
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
    die("<script>alert('Failed to create directory: $upload_dir. Check permissions.');</script>");
}

// Default Profile Picture Path
$default_image = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/default.jpg";
$target_file = $default_image;

// Handle File Upload
if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == UPLOAD_ERR_OK) {
    $imageFileType = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate File Type
    if (!in_array($imageFileType, $allowed_types)) {
        die("<script>alert('Invalid file type! Only JPG, JPEG, PNG, GIF allowed.');</script>");
    }

    // Generate Unique File Name
    $new_filename = uniqid("profile_", true) . "." . $imageFileType;
    $target_file = $upload_dir . $new_filename;

    // Move Uploaded File
    if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        die("<script>alert('Error uploading profile picture! Check permissions.');</script>");
    }

    // Convert File Path for Web Access
    $target_file = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/" . $username . "/" . $new_filename;
}

    // Use a relative path for storing in the database (important for portability)
    $target_file = str_replace(__DIR__, '', $target_file);

    // API Request to Add User (cURL)
    $url = 'https://localhost:7150/api/User/AddUser';
    $data = array(
        "userID" => 0,
        "username" => $username,
        "passwordHash" => $passwordHash,
        "email" => $email,
        "role" => $role,
        "profilePicture" => $target_file,
        "dateCreated" => date("Y-m-d\TH:i:s\Z")
    );

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Accept: */*"
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Disable SSL verification for local development (optional)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo "<script>alert('cURL Error: " . curl_error($ch) . "');</script>";
        exit();
    }

    // Close the cURL session
    curl_close($ch);

    // Handle the API response
    if ($response) {
        $json_response = json_decode($response, true);
        if (isset($json_response['statusMessage'])) {
            echo "<script>alert('" . $json_response['statusMessage'] . "');</script>";
        } else {
            echo "<script>alert('Unexpected response from the server.');</script>";
        }
    } else {
        echo "<script>alert('Error while adding user.');</script>";
    }
}
?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url("http://localhost/All_FOR_MUSIC/Logo/logo.jpeg");
            background-size: cover;
            background-position: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            position: relative;
            height: 100vh;
        }

        /* Blur effect */
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            z-index: -1;
        }

        .navbar {
            background-color: #111111;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand img {
            width: 40px;
            height: auto;
        }

        .container {
            max-width: 450px;
            margin-top: 80px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: #FFD700;
        }

        .form-title {
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
            color: #FFD700;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #FFD700;
            background-color: #222222;
            color: #FFD700;
            font-size: 16px;
        }

        .form-input:focus {
            border-color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: #FFD700;
            border: none;
            color: black;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #e6c200;
        }

        .profile-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-container input[type="file"] {
            display: none;
        }

        .profile-container label {
            font-size: 40px;
            color: #FFD700;
            cursor: pointer;
            transition: color 0.3s;
        }

        .profile-container label:hover {
            color: #e6c200;
        }

        .profile-preview img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #FFD700;
            margin-top: 20px;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer a {
            color: #FFD700;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .container {
                padding: 30px;
            }
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

    <!-- Registration Form -->
    <div class="container">
        <h2 class="form-title">User Registration</h2>
        <form action="Register.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <!-- Username Input -->
            <input class="form-input" type="text" name="username" placeholder="Username" required>

            <!-- Password Input with Toggle -->
            <div class="mb-3 input-group">
                <input type="password" class="form-input" id="password" name="password" placeholder="Password" required>
                <button class="btn btn-outline-warning" type="button" onclick="togglePassword()">👁</button>
            </div>

            <!-- Retype Password Input -->
            <input class="form-input" type="password" id="retype_password" name="retype_password" placeholder="Retype Password" required>

            <!-- Email Input -->
            <input class="form-input" type="email" name="email" placeholder="Email" required>

            <!-- Role Selection -->
            <select class="form-input" name="role" required>
    <?php if ($role) : ?>
        <option value="Admin">Admin</option>
    <?php endif; ?>
    <option value="Musician">Musician</option>
    <option value="Artist">Artist</option>
    <option value="Instructor">Instructor</option>
</select>


            <!-- Profile Picture Section -->
            <div class="profile-container">
                <label for="profile_picture" class="fas fa-camera"></label>
                <input class="form-input" type="file" name="profile_picture" id="profile_picture" onchange="previewImage()" required>
                <div id="profile_preview" class="profile-preview"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Register</button>
        </form>

        <!-- Footer -->
        <div class="form-footer">
            <p>Already have an account? <a href="login_check.php">Login here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }

        function previewImage() {
            var file = document.getElementById('profile_picture').files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                document.getElementById('profile_preview').innerHTML = '';  // Clear previous image
                document.getElementById('profile_preview').appendChild(img);
            }
            reader.readAsDataURL(file);
        }

        function validateForm() {
            var password = document.getElementById('password').value;
            var retypePassword = document.getElementById('retype_password').value;

            if (password !== retypePassword) {
                alert("Passwords do not match. Please try again.");
                return false;
            }

            alert("Registration successful!");
            return true;
        }
    </script>

</body>

</html>
