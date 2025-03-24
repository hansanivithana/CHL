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
<html>
<head>
    <title>User Search & Update</title>
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
    <form method="GET">
        <input type="text" name="search" placeholder="Search username..." required>
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($searchResults['data'])): ?>
        <form method="POST" enctype="multipart/form-data">
            <?php $user = $searchResults['data'][0]; ?>
            <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
            <input type="text" name="username" value="<?= $user['username'] ?>" required><br>
            <input type="email" name="email" value="<?= $user['email'] ?>" required><br>
            <input type="text" name="role" value="<?= $user['role'] ?? '' ?>"><br>
            
            <input type="password" name="password" id="password" placeholder="New Password" required>
            <input type="checkbox" onclick="togglePassword('password')"> Show Password<br>
            <input type="password" name="retype_password" id="retype_password" placeholder="Retype Password" required>
            <input type="checkbox" onclick="togglePassword('retype_password')"> Show Password<br>
            
            <input type="hidden" name="currentProfilePicture" value="<?= $user['profilePicture'] ?>">
            <img src="<?= $user['profilePicture'] ?>" width="100" id="preview"><br>
            <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)"><br>
            
            <button type="submit" name="update">Update User</button>
        </form>
    <?php endif; ?>
</body>
</html>
