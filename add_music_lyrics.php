<?php
session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Instructor', 'Artist', 'Musician'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$authorID = $user['userID'] ?? 1; // Default to 1 if not set

// Ensure 'dateCreated' field is set before using DateTime
$dateCreated = isset($user['dateCreated']) ? $user['dateCreated'] : '';
$formattedDate = 'Unknown Date';
if (!empty($dateCreated)) {
    $dateCreated = preg_replace('/\.\d{3}$/', '', $dateCreated);
    $date = new DateTime($dateCreated);
    $formattedDate = $date->format('F j, Y, g:i a'); // Example: March 4, 2025, 4:12 pm
}

// Define profile picture
$profilePictureURL = isset($user['profilePicture']) ? $user['profilePicture'] : 'default_profile.jpg';

// Set API URL
$api_url = "https://localhost:7150/api/MusicLyrics/AddMusicLyrics";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $price = $_POST['price'] ?? 0;

    // Create a unique folder for the song title
    $uploadDir = __DIR__ . "/uploads/" . preg_replace('/[^A-Za-z0-9]/', '_', $title);
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = "";
    if (!empty($_FILES['file']['name'])) {
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileName = basename($_FILES['file']['name']);
        $filePath = "uploads/" . preg_replace('/[^A-Za-z0-9]/', '_', $title) . "/" . $fileName;
        move_uploaded_file($fileTmpName, $filePath);
    }

    // Prepare API request data
    $postData = [
        "lyricID" => 0,
        "title" => $title,
        "content" => $content,
        "authorID" => $authorID,
        "price" => $price,
        "filePath" => $filePath,
        "dateAdded" => date('c')
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Accept: */*"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Decode response
    $responseData = json_decode($response, true);

    // Show response message
    if ($httpCode == 200) {
        echo "<p style='color:green;'>Music lyric added successfully!</p>";
        if (!empty($filePath)) {
            echo "<p>Uploaded file: <a href='$filePath' target='_blank'>$fileName</a></p>";
        }
    } else {
        echo "<p style='color:red;'>Error: " . $responseData['statusMessage'] . "</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Music Lyrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
        body {
            background-color: #121212;
            color: gold;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background: #1c1c1c;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(255, 215, 0, 0.5);
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        label {
            font-weight: bold;
        }
        input, textarea {
            background-color: #222;
            border: 1px solid gold;
            color: white;
        }
        input:focus, textarea:focus {
            box-shadow: 0px 0px 8px gold;
        }
        .btn-submit {
            background: gold;
            color: black;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-submit:hover {
            background: #d4af37;
        }
        .preview-container {
            text-align: center;
            margin-top: 15px;
            display: none;
        }
        .preview-container img, .preview-container iframe {
            width: 100%;
            max-height: 300px;
            border: 1px solid gold;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Add Music Lyrics</h2>
    
    <form id="lyricsForm" action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Author ID:</label>
            <input type="text" name="authorID" class="form-control" value="<?php echo htmlspecialchars($authorID); ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Title:</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Content:</label>
            <textarea name="content" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label>Price:</label>
            <input type="number" name="price" class="form-control" step="0.01">
        </div>

        <div class="mb-3">
            <label>Upload File:</label>
            <input type="file" name="file" class="form-control" id="fileInput" required>
        </div>

        <div class="preview-container" id="previewContainer">
            <p>Preview:</p>
            <div id="filePreview"></div>
        </div>

        <button type="submit" class="btn btn-submit w-100">Submit</button>
    </form>

    <!-- Alert messages -->
    <div id="alertBox" class="alert mt-3" style="display: none;"></div>
</div>

<script>
    $(document).ready(function() {
        $("#fileInput").change(function() {
            let file = this.files[0];
            if (!file) return;

            let reader = new FileReader();
            reader.onload = function(e) {
                let previewContainer = $("#previewContainer");
                let filePreview = $("#filePreview");
                let fileType = file.type;

                filePreview.html("");

                if (fileType.startsWith("image")) {
                    filePreview.html('<img src="' + e.target.result + '" class="img-fluid">');
                } else if (fileType === "application/pdf") {
                    filePreview.html('<iframe src="' + e.target.result + '" class="img-fluid" height="300"></iframe>');
                } else {
                    filePreview.html('<p class="text-warning">Preview not available for this file type.</p>');
                }

                previewContainer.show();
            };
            reader.readAsDataURL(file);
        });

        $("#lyricsForm").submit(function(event) {
            event.preventDefault();
            
            let title = $("input[name='title']").val().trim();
            let content = $("textarea[name='content']").val().trim();
            
            if (title === "" || content === "") {
                showAlert("Please fill out all required fields.", "danger");
                return;
            }
            
            showAlert("Submitting...", "info");
            setTimeout(function() {
                $("#lyricsForm")[0].submit();
            }, 1500);
        });

        function showAlert(message, type) {
            $("#alertBox").removeClass().addClass("alert alert-" + type).text(message).fadeIn();
            setTimeout(function() {
                $("#alertBox").fadeOut();
            }, 3000);
        }
    });
</script>

</body>
</html>

