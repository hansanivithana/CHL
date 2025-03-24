<?php
// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data with checks for null/empty values
    $packageID = isset($_POST['packageID']) ? trim($_POST['packageID']) : '';  // Default to empty string if not set
    $packageName = isset($_POST['packageName']) ? $_POST['packageName'] : '';  // Default to empty string if not set
    $description = isset($_POST['description']) ? $_POST['description'] : '';  // Default to empty string if not set
    $instructorID = isset($_POST['instructorID']) ? trim($_POST['instructorID']) : '';  // Default to empty string if not set
    $price = isset($_POST['price']) ? $_POST['price'] : '';  // Default to empty string if not set
    $learningMaterials = isset($_POST['learningMaterials']) ? $_POST['learningMaterials'] : '';
    $videoLessons = isset($_POST['videoLessons']) ? $_POST['videoLessons'] : '';

        // Handling file uploads for Learning Materials (Docs) and Video Lessons
        $learningMaterials = '';
        $videoLessons = '';
    
        // File paths for storing the uploaded documents and videos
        $uploadDirDocs = __DIR__ . "/uploads/learningMaterials/" . $packageName . "/";
        $uploadDirVideos = __DIR__ . "/uploads/videoLessons/" . $packageName . "/";
    
        // Ensure directories exist, create them if not
        if (!is_dir($uploadDirDocs) && !mkdir($uploadDirDocs, 0755, true)) {
            die("<script>alert('Failed to create directory for documents.');</script>");
        }
        if (!is_dir($uploadDirVideos) && !mkdir($uploadDirVideos, 0755, true)) {
            die("<script>alert('Failed to create directory for video lessons.');</script>");
        }
    
        // Handle Document Upload
        if (isset($_FILES["learningMaterials"]) && $_FILES["learningMaterials"]["error"] == UPLOAD_ERR_OK) {
            $docFileType = strtolower(pathinfo($_FILES["learningMaterials"]["name"], PATHINFO_EXTENSION));
            $allowedDocTypes = ['pdf', 'doc', 'docx'];
    
            // Validate Document Type
            if (!in_array($docFileType, $allowedDocTypes)) {
                echo "<script>alert('Invalid document type! Only PDF, DOC, DOCX allowed.');</script>";
                exit();
            }
    
            $newDocFilename = uniqid("doc_", true) . "." . $docFileType;
            $learningMaterials = $uploadDirDocs . $newDocFilename;
    
            // Move Uploaded Document
            if (!move_uploaded_file($_FILES["learningMaterials"]["tmp_name"], $learningMaterials)) {
                echo "<script>alert('Error uploading document!');</script>";
                exit();
            }
    
            // Convert to relative path for storing in database
            $learningMaterials = "uploads/learningMaterials/" . $packageName . "/" . $newDocFilename;
        }
    
        // Handle Video Upload
        if (isset($_FILES["videoLessons"]) && $_FILES["videoLessons"]["error"] == UPLOAD_ERR_OK) {
            $videoFileType = strtolower(pathinfo($_FILES["videoLessons"]["name"], PATHINFO_EXTENSION));
            $allowedVideoTypes = ['mp4', 'avi', 'mov'];
    
            // Validate Video Type
            if (!in_array($videoFileType, $allowedVideoTypes)) {
                echo "<script>alert('Invalid video type! Only MP4, AVI, MOV allowed.');</script>";
                exit();
            }
    
            $newVideoFilename = uniqid("video_", true) . "." . $videoFileType;
            $videoLessons = $uploadDirVideos . $newVideoFilename;
    
            // Move Uploaded Video
            if (!move_uploaded_file($_FILES["videoLessons"]["tmp_name"], $videoLessons)) {
                echo "<script>alert('Error uploading video!');</script>";
                exit();
            }
    
            // Convert to relative path for storing in database
            $videoLessons = "uploads/videoLessons/" . $packageName . "/" . $newVideoFilename;
        }
    
        // Validate required fields
        if (empty($packageID) || empty($packageName) || empty($description) || empty($instructorID) || empty($price)) {
            echo "<script>alert('Please fill all required fields!');</script>";
            exit();
        }
        
    // Prepare data for Learning Package API
    $url = 'https://localhost:7150/api/LearningPackage/AddLearningPackage';
    $data = array(
        "packageID" => 0, // Assuming this is auto-generated on the server
        "packageName" => $packageName,
        "description" => $description,
        "instructorID" => $instructorID,
        "price" => $price,
        "learningMaterials" => $learningMaterials,
        "videoLessons" => $videoLessons,
        "documentFile" => $documentFile, // Save document path in the database
        "videoFile" => $videoFile, // Save video path in the database
        "dateAdded" => date("Y-m-d\TH:i:s\Z")  // Current date in the required format
    );

    // Initialize cURL for adding the learning package
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Accept: */*"
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Disable SSL verification for local development (optional)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo "<script>alert('cURL Error: " . curl_error($ch) . "');</script>";
        exit();
    }

    // Close the cURL session
    curl_close($ch);

    // Handle the API response
    if ($response) {
        $json_response = json_decode($response, true);
        if (isset($json_response['statusMessage'])) {
            echo "<script>alert('" . $json_response['statusMessage'] . "');</script>";
        } else {
            echo "<script>alert('Unexpected response from the server.');</script>";
        }
    } else {
        echo "<script>alert('Error while adding learning package.');</script>";
    }
}
?>
