<?php
// api/GetProfilePicture.php

if (isset($_GET['username']) && isset($_GET['profilePicture'])) {
    // Decode URL parameters to handle spaces and special characters
    $username = urldecode($_GET['username']);
    $profilePicture = urldecode($_GET['profilePicture']);

    // Set your base directory for profile pictures
    $baseDir = "C:/wamp64/www/All_FOR_MUSIC/Uploads/Profile_Picture/";
    $filePath = $baseDir . $username . "/" . $profilePicture;

    // Check if the file exists
    if (file_exists($filePath)) {
        $fileInfo = pathinfo($filePath);
        $extension = strtolower($fileInfo['extension']);

        // Set the appropriate MIME type
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;
            default:
                $mime = 'application/octet-stream';
        }
        header('Content-Type: ' . $mime);
        readfile($filePath);
        exit;
    } else {
        // Fall back to a default profile picture if the specified file is not found
        $defaultFile = $baseDir . "default_profile_picture.jpg";
        if (file_exists($defaultFile)) {
            $defaultExt = strtolower(pathinfo($defaultFile, PATHINFO_EXTENSION));
            switch ($defaultExt) {
                case 'jpg':
                case 'jpeg':
                    $mime = 'image/jpeg';
                    break;
                case 'png':
                    $mime = 'image/png';
                    break;
                case 'gif':
                    $mime = 'image/gif';
                    break;
                default:
                    $mime = 'application/octet-stream';
            }
            header('Content-Type: ' . $mime);
            readfile($defaultFile);
            exit;
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Image not found.";
            exit;
        }
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "Missing parameters.";
    exit;
}
?>
