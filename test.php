<?php
// test.php - for debugging the file path

// Hardcode the test username and image filename (adjust these values as needed)
$username = "Chamodha Kulananda";
$profilePicture = "Screenshot 2025-02-20 204451.png";

// Construct the full path
$baseDir = "C:/wamp64/www/All_FOR_MUSIC/Uploads/Profile_Picture/";
$filePath = $baseDir . $username . "/" . $profilePicture;

echo "<h2>Debugging Profile Picture Path</h2>";
echo "<p>Constructed File Path: <strong>$filePath</strong></p>";

// Check if the file exists and output the result
if (file_exists($filePath)) {
    echo "<p style='color: green;'>File exists!</p>";
    // Optionally, display the image
    $imageUrl = "retrievePicture.php?username=" . urlencode($username) . "&profilePicture=" . urlencode($profilePicture);
    echo "<img src='{$imageUrl}' alt='Profile Picture' style='width:200px;height:auto;'>";
} else {
    echo "<p style='color: red;'>File does not exist!</p>";
}
?>
