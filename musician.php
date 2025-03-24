<?php
session_start();

// Ensure the user is logged in and has the 'Musician' role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Musician') {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$requestedEntityID = $user['userID'];  // Get the musician's user ID

// Function to fetch the musician's requests
function fetchRequests($requestedEntityID) {
    $apiUrl = "https://localhost:7150/api/Request/GetRequestsByRequestedEntityId?requestedEntityID=$requestedEntityID";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Fetch requests
$requests = fetchRequests($requestedEntityID);

// Initialize alert message
$alertMessage = "";

// Check for Accepted or Declined status
if (!empty($requests['data'])) {
    foreach ($requests['data'] as $request) {
        if ($request['status'] == 'Accepted') {
            $alertMessage = "Your request (ID: {$request['requestID']}) has been ACCEPTED!";
            break; // Show only the first accepted request message
        } elseif ($request['status'] == 'Declined') {
            $alertMessage = "Your request (ID: {$request['requestID']}) has been DECLINED!";
            break; // Show only the first declined request message
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musician Dashboard | All For Music</title>
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

    <!-- Show alert message if available -->
    <?php if (!empty($alertMessage)): ?>
        <script>
            alert("<?php echo $alertMessage; ?>");
        </script>
    <?php endif; ?>

    <div class="container">
        <div class="dashboard-title">Musician Dashboard</div>
        
        <div class="profile-section">
            <img src="<?= $user['profilePicture'] ?>" alt="Profile Picture" class="profile-pic">
            <div class="profile-name"><?= $user['username'] ?></div>
            <div class="profile-email"><?= $user['email'] ?></div>
        </div>

        <div class="image-buttons">
            <a href="added_music.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/RecentlyAddedMusic.jpg" alt="Add Music" class="img-button">
            </a>
            <a href="AddRequest.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/RequestFromUS.png" alt="Manage Music" class="img-button">
            </a>
            <a href="Instrument.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/SellInstrument.png" alt="View Requests" class="img-button">
            </a>
            <a href="add_music_lyrics.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/AddMusicLhyircs.png" alt="Add Music Lyrics" class="img-button">
            </a>
            <a href="submit_feedback.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/FeedBack.png" alt="Feedback" class="img-button">
            </a>
            
            <a href="logout.php">
                <img src="http://localhost/All_FOR_MUSIC/Icons/LogOut.png" alt="Log Out" class="img-button">
            </a>
        </div>
    </div>
</body>  
</html>
