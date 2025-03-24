<?php

session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Musician'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$dateCreated = isset($user['dateCreated']) ? preg_replace('/\.\d{3}$/', '', $user['dateCreated']) : '';
$formattedDate = !empty($dateCreated) ? (new DateTime($dateCreated))->format('F j, Y, g:i a') : 'Unknown Date';
$profilePictureURL = $user['profilePicture'] ?? 'default_profile.jpg';

// Store user info in session
$_SESSION['user'] = [
    'userID' => $user['userID'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'profilePicture' => $profilePictureURL,
    'dateCreated' => $user['dateCreated']
];

$upload_dir = __DIR__ . "/Uploads/Profile_Picture/";
$default_image = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/default.jpg";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $feedbackText = $_POST['feedbackText'];
    $userID = $_POST['userID'];
    $dateSubmitted = date("Y-m-d\TH:i:s\Z");

    // Prepare the data array
    $data = array(
        "feedbackID" => 0,
        "userID" => $userID,
        "feedbackText" => $feedbackText,
        "dateSubmitted" => $dateSubmitted
    );

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "https://localhost:7150/api/Feedback/SubmitFeedback");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'accept: */*'
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

    // Execute cURL request
    $response = curl_exec($ch);
    curl_close($ch);

    // Handle the response
    $responseData = json_decode($response, true);
    if ($responseData && $responseData['statusCode'] == 200) {
        echo "<script>alert('Feedback submitted successfully!');</script>";
    } else {
        echo "<script>alert('There was an issue submitting your feedback. Please try again later.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #000;
            color: #f1c40f;
        }
        .container {
            background-color: #222;
            padding: 30px;
            border-radius: 8px;
            margin-top: 50px;
        }
        .form-group label {
            color: #f1c40f;
        }
        .form-group input, .form-group textarea {
            background-color: #333;
            color: #f1c40f;
        }
        .btn-submit {
            background-color: #f1c40f;
            color: black;
        }
        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-info img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Submit Feedback</h2>

    <!-- Display User Details -->
    <div class="profile-info">
        <img src="<?php echo $profilePictureURL; ?>" alt="Profile Picture">
        <h4><?php echo $user['username']; ?></h4>
        <p>Email: <?php echo $user['email']; ?></p>
        <p>Role: <?php echo $user['role']; ?></p>
    </div>

    <form method="POST" id="feedbackForm">
        <div class="mb-3">
            <label for="userID" class="form-label">User ID</label>
            <!-- Automatically populate the userID field with the current logged-in user's ID -->
            <input type="number" class="form-control" id="userID" name="userID" value="<?php echo $user['userID']; ?>" readonly required>
        </div>
        <div class="mb-3">
            <label for="feedbackText" class="form-label">Feedback</label>
            <textarea class="form-control" id="feedbackText" name="feedbackText" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-submit btn-block">Submit Feedback</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
