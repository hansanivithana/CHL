<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $songTitle = $_POST['songTitle'];
    $artistName = $_POST['artistName'];
    $genre = $_POST['genre'];
    $difficultyLevel = $_POST['difficultyLevel'];
    $dateAdded = date("c"); // Get current date in ISO format

    // File upload handling
    $targetDir = "notations/" . preg_replace("/[^a-zA-Z0-9]/", "_", $songTitle) . "/"; // Create folder using song title
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $targetFile = $targetDir . basename($_FILES["notation"]["name"]);
    if (move_uploaded_file($_FILES["notation"]["tmp_name"], $targetFile)) {
        $message = "Notation uploaded successfully.";
        $msgType = "success";
    } else {
        $message = "Error uploading notation.";
        $msgType = "danger";
    }

    // Prepare data array
    $data = [
        "notationID" => 0,
        "songTitle" => $songTitle,
        "artistName" => $artistName,
        "genre" => $genre,
        "difficultyLevel" => $difficultyLevel,
        "notation" => $targetFile, // Save file path
        "dateAdded" => $dateAdded
    ];

    // cURL request
    $url = "https://localhost:7150/api/LatestSongNotations/AddNotation";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: */*'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showMessage('$message', '$msgType');
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Latest Music Notation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        /* Navbar */
        .navbar {
            background-color: black;
            padding: 10px 20px;
        }

        .navbar-brand img {
            width: 60px;
            height: auto;
        }

        /* Form styling */
        .form-container {
            background: rgba(0, 0, 0, 0.8);
            color: gold;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            margin: auto;
            margin-top: 5%;
        }

        input, button {
            margin-bottom: 10px;
        }

        button {
            background-color: gold;
            color: black;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: darkgoldenrod;
        }

        /* Image preview */
        #preview {
            max-width: 200px;
            display: block;
            margin-top: 10px;
        }

        /* Message styling */
        .message-container {
            width: 50%;
            margin: auto;
            margin-top: 10px;
            text-align: center;
        }
    </style>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement("div");
            messageDiv.className = `alert alert-${type}`;
            messageDiv.innerText = message;
            document.querySelector(".message-container").appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">
            <img src="http://localhost/All_FOR_MUSIC/Logo/logo.jpeg" alt="Logo">
        </a>
    </nav>

    <div class="message-container"></div>

    <div class="form-container">
        <h2 class="text-center">Add Latest Music Notation</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Song Title:</label>
            <input type="text" name="songTitle" class="form-control" required>

            <label>Artist Name:</label>
            <input type="text" name="artistName" class="form-control" required>

            <label>Genre:</label>
            <input type="text" name="genre" class="form-control" required>

            <label>Difficulty Level:</label>
            <input type="text" name="difficultyLevel" class="form-control" required>

            <label>Upload Notation (Image):</label>
            <input type="file" name="notation" class="form-control" accept="image/*" onchange="previewImage(event)" required>

            <img id="preview" src="" alt="Image Preview"><br>

            <button type="submit" class="btn btn-block">Add Notation</button>
        </form>
    </div>
</body>
</html>
