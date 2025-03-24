<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('http://localhost/All_FOR_MUSIC/Logo/logo.jpeg') no-repeat center center/cover;
            backdrop-filter: blur(10px);
            color: gold;
            text-align: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start; /* Adjusts content positioning */
            padding-top: 80px; /* Adds space to the top of the body to avoid navbar overlap */
        }

        .navbar {
            background-color: black;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.5);
            position: fixed; /* Fixes the navbar at the top */
            top: 0;
            width: 100%;
            z-index: 1000; /* Keeps navbar above other content */
        }

        .navbar-brand img {
            height: 50px;
            filter: drop-shadow(0 0 5px gold);
        }

        table {
            width: 90%;
            margin: 30px auto; /* Adjusts the table margin */
            border-collapse: collapse;
            background: rgba(0, 0, 0, 0.7); /* Transparent background */
            color: gold;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
            border-radius: 10px;
            overflow: hidden;
            max-width: 100%; /* Ensures table is responsive */
        }

        th, td {
            padding: 15px;
            border: 1px solid gold;
            text-align: center;
        }

        th {
            background-color: #DAA520;
            color: black;
            font-size: 1.1rem;
        }

        td {
            font-size: 1rem;
        }

        td img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }

        td img:hover {
            transform: scale(1.1);
        }

        td a {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            font-size: 1rem;
        }

        td a:hover {
            background: darkred;
        }

        @media (max-width: 768px) {
            table {
                width: 100%;
            }
            th, td {
                padding: 10px;
                font-size: 0.9rem;
            }
            td img {
                width: 50px;
                height: 50px;
            }
            td a {
                padding: 6px 12px;
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

    <!-- PHP to Fetch and Display Users -->
    <?php
    // API URL
    $api_url = "https://localhost:7150/api/User/ViewUsers";

    // Initialize cURL session
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local testing
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json"
    ]);

    // Execute cURL request
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Decode JSON response
    $data = json_decode($response, true);

    // Check for errors
    if ($http_status !== 200 || !isset($data['data'])) {
        echo "<p style='color: red;'>Error fetching user data!</p>";
        exit;
    }

    // Display Users
    echo "<table>";
    echo "<tr><th>Profile Picture</th><th>UserID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr>";

    foreach ($data['data'] as $user) {
        $userID = isset($user['userID']) ? (int)$user['userID'] : 0;
        $username = isset($user['username']) ? htmlspecialchars($user['username']) : "Unknown";
        $email = isset($user['email']) ? htmlspecialchars($user['email']) : "No Email";
        $role = isset($user['role']) ? htmlspecialchars($user['role']) : "No Role";

        // Fix Profile Picture Path
        $defaultProfilePicture = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/default.jpg";

        if (isset($user['profilePicture']) && !empty($user['profilePicture'])) {
            // Adjust file path for web access
            $profilePicture = str_replace("C:/wamp64/www/", "http://localhost/", $user['profilePicture']);
            $profilePicture = str_replace(" ", "%20", $profilePicture); // Fix spaces
        } else {
            $profilePicture = $defaultProfilePicture;
        }

        echo "<tr>";
        echo "<td><img src='$profilePicture' onerror=\"this.onerror=null; this.src='$defaultProfilePicture';\"></td>";
        echo "<td>$userID</td>";
        echo "<td>$username</td>";
        echo "<td>$email</td>";
        echo "<td>$role</td>";
        echo "<td>
                <a href='deleteuser.php?id=$userID' onclick=\"return confirm('Are you sure you want to remove this user?');\">Delete</a>
              </td>";
        echo "</tr>";
    }

    echo "</table>";
    ?>

</body>
</html>
