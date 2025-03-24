<?php
session_start();

// Ensure the user is logged in and has the 'Admin' role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Artist') {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];

// Ensure the 'dateCreated' field is set and not null before using DateTime
$dateCreated = isset($user['dateCreated']) ? $user['dateCreated'] : '';  // Default date if null

// If dateCreated is not empty, format it using DateTime
if (!empty($dateCreated)) {
    // Remove milliseconds if they exist (optional based on your DB format)
    $dateCreated = preg_replace('/\.\d{3}$/', '', $dateCreated);  // Removes the milliseconds part

    // Now create the DateTime object
    $date = new DateTime($dateCreated);
    $formattedDate = $date->format('F j, Y, g:i a'); // Example: March 4, 2025, 4:12 pm
} else {
    $formattedDate = 'Unknown Date';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | All For Music</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: white;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .profile-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid gold;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        .profile-name {
            font-size: 1.5rem;
            margin-top: 10px;
            font-weight: bold;
        }

        .profile-email {
            font-size: 1rem;
            color: #bbb;
        }

        .image-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .img-button {
            width: 250px;
            height: 250px;
            border-radius: 15px;
            object-fit: cover;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .img-button:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-title">Artist </div>
        
        <div class="profile-section">
            <img src="<?= $user['profilePicture'] ?>" alt="Profile Picture" class="profile-pic">
            <div class="profile-name"><?= $user['username'] ?></div>
            <div class="profile-email"><?= $user['email'] ?></div>
        </div>

        <div class="image-buttons">
        <a href="added_music.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/RecentlyAddedMusic.jpg" alt="View Music" class="img-button">
            </a>

            <a href="viewRequestd.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/ViewRequest.png" alt="View Requests" class="img-button">
            </a>
            <a href="add_music_lyrics.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/AddMusicLhyircs.png" alt="Add Music" class="img-button">
    </a>
            <a href="view_requests.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/Response.png" alt="Give an Response" class="img-button">
    </a>

    <a href="logout.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/LogOut.png" alt="View Requests" class="img-button">
    </a>

        </div>
    </div>
</body>
</html>