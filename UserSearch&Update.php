<?php
session_start();

// API URLs
$searchApiUrl = "https://localhost:7150/api/User/SearchUsersByName";
$updateApiUrl = "https://localhost:7150/api/User/UpdateUser/";

// Disable SSL Verification
$sslOptions = [
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0
];

// Handle user search
if (isset($_GET['search'])) {
    $username = $_GET['search'];
    $ch = curl_init("$searchApiUrl?username=" . urlencode($username));
    curl_setopt_array($ch, $sslOptions + [CURLOPT_RETURNTRANSFER => true]);
    $response = curl_exec($ch);
    curl_close($ch);
    $searchResults = json_decode($response, true);
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $userID = $_POST['userID'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $retypePassword = $_POST['retype_password'];
    
    // Password hashing
    if ($password !== $retypePassword) {
        die("<script>alert('Passwords do not match!');</script>");
    }
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Handle profile picture upload
    $profilePicture = $_POST['currentProfilePicture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = __DIR__ . "/Uploads/Profile_Picture/" . $username . "/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $new_filename = uniqid("profile_", true) . "_" . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $new_filename;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        $profilePicture = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/$username/$new_filename";
    }

    // Prepare data for API
    $data = [
        "userID" => (int)$userID,
        "username" => $username,
        "passwordHash" => $hashedPassword,
        "email" => $email,
        "role" => $role,
        "profilePicture" => $profilePicture,
        "dateCreated" => date("c")
    ];
    
    $ch = curl_init($updateApiUrl . $userID);
    curl_setopt_array($ch, $sslOptions + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $updateResult = json_decode($response, true);
    echo "<script>alert('" . $updateResult['statusMessage'] . "');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search & Update</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: url("http://localhost/All_FOR_MUSIC/Logo/logo.jpeg") no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: blur(8px);
            color: gold;
        }
        .navbar {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 10px;
        }
        .container {
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            margin-top: 50px;
            box-shadow: 0px 0px 10px gold;
        }
        .btn-custom {
            background-color: gold;
            color: black;
            font-weight: bold;
        }
        .btn-custom:hover {
            background-color: black;
            color: gold;
            transition: 0.3s;
        }
        img {
            border-radius: 10px;
            box-shadow: 0px 0px 10px gold;
        }
    </style>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                document.getElementById('preview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        function togglePassword(id) {
            var field = document.getElementById(id);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">
            <img src="http://localhost/All_FOR_MUSIC/Logo/logo.jpeg" alt="Logo" width="50" height="50">
        </a>
    </nav>

    <div class="container">
        <h2 class="text-center">User Search & Update</h2>
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search username..." required>
                <button type="submit" class="btn btn-custom">Search</button>
            </div>
        </form>

        <?php if (!empty($searchResults['data'])): ?>
            <form method="POST" enctype="multipart/form-data">
                <?php $user = $searchResults['data'][0]; ?>
                <input type="hidden" name="userID" value="<?= $user['userID'] ?>">

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label>Role</label>
                    <input type="text" name="role" class="form-control" value="<?= $user['role'] ?? '' ?>">
                </div>
                
                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <input type="checkbox" onclick="togglePassword('password')"> Show Password
                </div>
                
                <div class="mb-3">
                    <label>Retype Password</label>
                    <input type="password" name="retype_password" id="retype_password" class="form-control" required>
                    <input type="checkbox" onclick="togglePassword('retype_password')"> Show Password
                </div>
                
                <div class="mb-3">
                    <label>Current Profile Picture</label><br>
                    <input type="hidden" name="currentProfilePicture" value="<?= $user['profilePicture'] ?>">
                    <img src="<?= $user['profilePicture'] ?>" width="100" id="preview" class="img-thumbnail"><br>
                </div>
                
                <div class="mb-3">
                    <label>Upload New Profile Picture</label>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*" onchange="previewImage(event)">
                </div>
                
                <button type="submit" name="update" class="btn btn-custom w-100">Update User</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
