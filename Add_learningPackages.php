<?php
session_start();

// Ensure the user is logged in and has the 'Admin' or 'Instructor' role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Instructor'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];

// Ensure 'dateCreated' field is set before using DateTime
$dateCreated = isset($user['dateCreated']) ? $user['dateCreated'] : '';

$formattedDate = 'Unknown Date';
if (!empty($dateCreated)) {
    // Remove milliseconds if they exist
    $dateCreated = preg_replace('/\.\d{3}$/', '', $dateCreated);
    $date = new DateTime($dateCreated);
    $formattedDate = $date->format('F j, Y, g:i a'); // Example: March 4, 2025, 4:12 pm
}

// Define the profile picture if available
$profilePictureURL = isset($user['profilePicture']) ? $user['profilePicture'] : 'default_profile.jpg';

// Store user info in session (ensure it contains necessary values)
$_SESSION['user'] = [
    'userID' => $user['userID'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'profilePicture' => $profilePictureURL,
    'dateCreated' => $user['dateCreated']
];

// Set API URL
$api_url = "https://localhost:7150/api/User/ViewUsers";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and Validate Input
    $learningPackageName = isset($_POST['learningPackageName']) ? trim($_POST['learningPackageName']) : '';
    $instructorID = isset($_POST['instructorID']) ? trim($_POST['instructorID']) : $user['userID'];
    $instructorName = isset($_POST['instructorName']) ? trim($_POST['instructorName']) : $user['username'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $videoURL = isset($_POST['videoURL']) ? trim($_POST['videoURL']) : '';
    $materialURL = isset($_POST['materialURL']) ? trim($_POST['materialURL']) : '';
    $createdDate = date("Y-m-d\TH:i:s\Z");

    if (empty($learningPackageName) || empty($instructorID) || empty($instructorName) || empty($description) || empty($videoURL) || empty($materialURL)) {
        echo "<script>alert('Please fill all required fields!');</script>";
        exit();
    }

    // Prepare API Request
    $url = 'https://localhost:7150/api/LearningPackage/AddLearningPackage';
    $data = [
        "packageID" => 0,
        "learningPackageName" => $learningPackageName,
        "instructorID" => $instructorID,
        "instructorName" => $instructorName,
        "learningMaterials" => $materialURL,  // Google Drive Link
        "videos" => $videoURL,  // YouTube Link
        "description" => $description,
        "createdDate" => $createdDate
    ];

    // Send API Request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        die("<script>alert('cURL Error: $curl_error');</script>");
    }

    // Handle API Response
    $json_response = json_decode($response, true);
    if (isset($json_response['statusMessage'])) {
        echo "<script>alert('" . $json_response['statusMessage'] . "');</script>";
    } else {
        echo "<script>alert('Unexpected server response.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Learning Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            position: relative;
            background-image: url("http://localhost/All_FOR_MUSIC/Logo/logo.jpeg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .background-blur {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            filter: blur(10px);
            z-index: -1;
        }

        .container {
            position: relative;
            z-index: 2;
        }

        .navbar {
            background-color: black;
        }

        .navbar-brand img {
            width: 100px;
            height: auto;
        }

        .navbar-dark .navbar-nav .nav-link {
            color: #f8c42c; /* Gold color for links */
        }

        .navbar-dark .navbar-nav .nav-link:hover {
            color: #fff; /* White color on hover */
        }

        .form-control {
            background-color: #fff; /* White background */
            color: #333; /* Dark text color */
            border: 2px solid #f8c42c; /* Gold border */
        }

        .form-control:focus {
            background-color: #fff; /* White background on focus */
            border-color: #f8c42c;
            box-shadow: 0 0 5px 2px #f8c42c;
        }

        .btn-primary {
            background-color: #f8c42c;
            border: none;
        }

        .btn-primary:hover {
            background-color: #d1a100;
        }

        .form-label {
            color: #f8c42c;
        }

        .p-4 {
            background-color: rgba(0, 0, 0, 0.7); /* Slightly dark background for the form */
            border-radius: 10px;
        }

        h2 {
            color: #f8c42c;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        iframe {
            border: none;
        }
    </style>
    <script>
        function previewVideo(event) {
            let video = document.getElementById('videoPreview');
            let url = event.target.value.trim();
            if (url.includes("youtube.com") || url.includes("youtu.be")) {
                video.src = url.replace("watch?v=", "embed/");
                video.style.display = 'block';
            } else {
                video.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <!-- Background blur -->
    <div class="background-blur"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">
            <img src="http://localhost/All_FOR_MUSIC/Logo/logo.jpeg" alt="Logo">
        </a>
    </nav>

    <!-- Add Learning Package Form -->
    <div class="container mt-5">
        <h2 class="text-center">Add Learning Package</h2>
        <form action="Add_learningPackages.php" method="POST" class="p-4 border rounded">
            <div class="mb-3">
                <label class="form-label">Package Name:</label>
                <input type="text" name="learningPackageName" class="form-control" required>
            </div>
            
            <div class="mb-3">
    <label class="form-label">Instructor ID:</label>
    <input type="text" name="instructorID" class="form-control" required value="<?php echo isset($_SESSION['user']['userID']) ? $_SESSION['user']['userID'] : ''; ?>" readonly>
</div>

<div class="mb-3">
    <label class="form-label">Instructor Name:</label>
    <input type="text" name="instructorName" class="form-control" required value="<?php echo isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : ''; ?>" readonly>
</div>


            <div class="mb-3">
                <label class="form-label">YouTube Video URL:</label>
                <input type="url" name="videoURL" class="form-control" placeholder="https://www.youtube.com/watch?v=..." oninput="previewVideo(event)" required>
                <iframe id="videoPreview" width="100%" height="250" style="display:none; margin-top:10px;"></iframe>
            </div>
            <div class="mb-3">
                <label class="form-label">Google Drive Learning Material URL:</label>
                <input type="url" name="materialURL" class="form-control" placeholder="https://drive.google.com/file/d/..." required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description:</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Learning Package</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
