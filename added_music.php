
<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Please log in to continue.'); window.location.href = 'login.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$authorID = $user['userID'] ?? 1; // Auto-fill logged-in user ID

// API Endpoint
$apiUrl = "https://localhost:7150/api/MusicLyrics/GetMusicLyricsByAuthorId/$authorID";

// cURL request to fetch user lyrics
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
$response = curl_exec($ch);
curl_close($ch);

$lyricsData = json_decode($response, true);

// Check if data is available
$lyrics = $lyricsData['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Music Lyrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: gold;
        }
        .card {
            background: #1c1c1c;
            color: gold;
            border: 1px solid gold;
            margin-bottom: 15px;
            animation: fadeIn 1s ease-in-out;
        }
        .dropdown-menu {
            background-color: #222;
        }
        .dropdown-item {
            color: white;
        }
        .dropdown-item:hover {
            background-color: gold;
            color: black;
        }
        .preview-container img, .preview-container iframe {
            width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">My Music Lyrics</h2>

    <?php if (empty($lyrics)): ?>
        <p class="text-center text-warning">No lyrics found.</p>
    <?php else: ?>
        <?php foreach ($lyrics as $lyric): ?>
            <div class="card p-3">
                <h4><?php echo htmlspecialchars($lyric['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars(substr($lyric['content'], 0, 150))); ?>...</p>
                <p><strong>Price:</strong> $<?php echo number_format($lyric['price'], 2); ?></p>
                <p><small>Date Added: <?php echo date("F j, Y", strtotime($lyric['dateAdded'])); ?></small></p>

                <?php if (!empty($lyric['filePath'])): ?>
                    <div class="preview-container">
                        <?php
                        $fileType = pathinfo($lyric['filePath'], PATHINFO_EXTENSION);
                        if (in_array(strtolower($fileType), ['jpg', 'jpeg', 'png', 'gif'])) {
                            echo "<img src='" . htmlspecialchars($lyric['filePath']) . "' alt='Image Preview'>";
                        } elseif ($fileType === "pdf") {
                            echo "<iframe src='" . htmlspecialchars($lyric['filePath']) . "'></iframe>";
                        } else {
                            echo "<p class='text-warning'>File preview not available.</p>";
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="dropdown mt-2">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Manage
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="update_lyric.php?id=<?php echo $lyric['lyricID']; ?>">Update</a></li>
                        <li><a class="dropdown-item text-danger delete-lyric" data-id="<?php echo $lyric['lyricID']; ?>" href="#">Delete</a></li>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function() {
        $(".delete-lyric").click(function(event) {
            event.preventDefault();
            let lyricID = $(this).data("id");

            if (confirm("Are you sure you want to delete this lyric?")) {
                $.ajax({
                    url: "delete_lyric.php",
                    type: "POST",
                    data: { id: lyricID },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            }
        });
    });
</script>

</body>
</html>
