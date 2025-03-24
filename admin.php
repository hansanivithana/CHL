<?php
session_start();

// Ensure the user is logged in and has the 'Admin' role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
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
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-image: url('http://localhost/All_FOR_MUSIC/Logo/logo.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            backdrop-filter: blur(8px);
            height: 100vh;
        }

        .navbar-custom {
            background-color: #000;
        }

        .navbar-custom a {
            color: gold;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
        }

        .card-body {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .navbar-custom a:hover {
            color: #f39c12;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
        }

        .list-group-item {
            background-color: #333;
            color: gold;
            border: none;
            transition: background-color 0.3s ease;
        }

        .list-group-item:hover {
            background-color: #f39c12;
            color: #333;
        }

        .container {
            margin-top: 50px;
            z-index: 2;
        }

        .navbar-brand {
            color: gold;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .navbar-toggler-icon {
            background-color: gold;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="#">All For Music Admin</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?= $user['profilePicture'] ?>" alt="Profile Picture" class="profile-pic mb-3">
                        <h3><?= $user['username'] ?></h3>
                        <p><?= $user['email'] ?></p>
                        <p>Role: <?= $user['role'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <!-- Admin Options Panel -->
                <div class="list-group">
                    <a href="Register.php?role=Admin" class="list-group-item list-group-item-action">Add Users</a>
                    <a href="Add_learningPackages.php" class="list-group-item list-group-item-action">Add Learning Packages</a>
                    <a href="viewuser.php" class="list-group-item list-group-item-action">View Users</a>
                    <a href="UserSearch&Update.php" class="list-group-item list-group-item-action">Update Users</a>
                    <a href="ViewLearningPackages.php" class="list-group-item list-group-item-action">View Learning Packages</a>
                    <a href="Instrument.php" class="list-group-item list-group-item-action">Add instrumnet</a>
                    <a href="InstrumentUpdate.php" class="list-group-item list-group-item-action">Update instrumnet</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
