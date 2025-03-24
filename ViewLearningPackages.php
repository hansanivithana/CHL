
<?php

session_start();

// Ensure the user is logged in and has the 'Admin' or 'Instructor' role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Instructor'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$userID = $user['userID']; // Store user ID for filtering if needed

// Ensure 'dateCreated' field is set before using DateTime
$dateCreated = isset($user['dateCreated']) ? $user['dateCreated'] : '';

$formattedDate = 'Unknown Date';
if (!empty($dateCreated)) {
    // Remove milliseconds if they exist
    $dateCreated = preg_replace('/\.\d{3}$/', '', $dateCreated);
    $date = new DateTime($dateCreated);
    $formattedDate = $date->format('F j, Y, g:i a'); // Example: March 4, 2025, 4:12 pm
}

// Define the profile picture if available
$profilePictureURL = isset($user['profilePicture']) ? $user['profilePicture'] : 'default_profile.jpg';

// Store user info in session (ensure it contains necessary values)
$_SESSION['user'] = [
    'userID' => $user['userID'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'profilePicture' => $profilePictureURL,
    'dateCreated' => $user['dateCreated']
];

$apiBaseUrl = "https://localhost:7150/api/LearningPackage";

// Fetch Learning Packages based on role
if ($user['role'] === 'Admin') {
    // Admin can see all packages
    $apiUrl = "$apiBaseUrl/GetLearningPackages";
} else {
    // Instructors can only see their own packages
    $apiUrl = "$apiBaseUrl/GetLearningPackagesByInstructor/$userID";
}

$curl = curl_init($apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($curl);
curl_close($curl);
$packages = json_decode($response, true)['data'] ?? [];

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $curl = curl_init("$apiBaseUrl/RemoveLearningPackage/$id");
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($curl);
    curl_close($curl);
    header("Location: ViewLearningPackages.php");
    exit();
}

// Handle Update Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $updateData = json_encode([
        "packageID" => $_POST['packageID'],
        "learningPackageName" => $_POST['learningPackageName'],
        "instructorID" => $_POST['instructorID'],
        "instructorName" => $_POST['instructorName'],
        "learningMaterials" => $_POST['learningMaterials'],
        "videos" => $_POST['videos'],
        "description" => $_POST['description'],
        "createdDate" => date("c")
    ]);
    
    $curl = curl_init("$apiBaseUrl/UpdateLearningPackage/" . $_POST['packageID']);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $updateData);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($curl);
    curl_close($curl);
    header("Location: ViewLearningPackages.php");
    exit();
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>Manage Learning Packages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background: url("http://localhost/All_FOR_MUSIC/Logo/logo.jpeg") no-repeat center center fixed;
            background-size: cover;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
        }
        .navbar {
            background-color: #111;
        }
        .navbar-brand img {
            height: 50px;
        }
        .container {
            position: relative;
            z-index: 1;
            background: rgba(0, 0, 0, 0.8);
            color: gold;
            padding: 20px;
            border-radius: 10px;
        }
        .table {
            color: gold;
        }
        .btn-primary {
            background-color: gold;
            border-color: gold;
            color: black;
        }
        .btn-danger {
            background-color: red;
            border-color: red;
        }
        .modal-content {
            background-color: #222;
            color: gold;
        }
    </style>
</head>
<body>
<div class="overlay"></div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">
        <img src="http://localhost/All_FOR_MUSIC/Logo/logo.jpeg" alt="Logo">
    </a>
</nav>

<div class="container mt-4">
    <h2 class="text-center">Learning Packages</h2>
    <div id="messageBox" class="alert d-none"></div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Package Name</th>
                <th>Instructor</th>
                <th>Materials</th>
                <th>Videos</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?= $package['packageID'] ?></td>
                    <td><?= htmlspecialchars($package['learningPackageName']) ?></td>
                    <td><?= htmlspecialchars($package['instructorName']) ?></td>
                    <td><a href="<?= htmlspecialchars($package['learningMaterials']) ?>" target="_blank">View Materials</a></td>
                    <td><a href="<?= htmlspecialchars($package['videos']) ?>" target="_blank">Watch Video</a></td>
                    <td><?= htmlspecialchars($package['description']) ?></td>
                    <td>
                        <a href="?delete=<?= $package['packageID'] ?>" class="btn btn-danger btn-sm" onclick="showMessage('Deleted successfully!', 'danger')">Delete</a>
                        <button class="btn btn-primary btn-sm" onclick="editPackage(<?= htmlspecialchars(json_encode($package)) ?>)">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Learning Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="packageID" id="packageID">
                    <div class="mb-3">
                        <label>Package Name</label>
                        <input type="text" name="learningPackageName" id="learningPackageName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Instructor ID</label>
                        <input type="text" name="instructorID" id="instructorID" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Instructor Name</label>
                        <input type="text" name="instructorName" id="instructorName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Learning Materials (Google Drive Link)</label>
                        <input type="url" name="learningMaterials" id="learningMaterials" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Videos (YouTube Link)</label>
                        <input type="url" name="videos" id="videos" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="description" class="form-control" required></textarea>
                    </div>
                    <button type="submit" name="update" class="btn btn-primary" onclick="showMessage('Updated successfully!', 'success')">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editPackage(packageData) {
    document.getElementById('packageID').value = packageData.packageID;
    document.getElementById('learningPackageName').value = packageData.learningPackageName;
    document.getElementById('instructorID').value = packageData.instructorID;
    document.getElementById('instructorName').value = packageData.instructorName;
    document.getElementById('learningMaterials').value = packageData.learningMaterials;
    document.getElementById('videos').value = packageData.videos;
    document.getElementById('description').value = packageData.description;
    new bootstrap.Modal(document.getElementById('updateModal')).show();
}

function showMessage(message, type) {
    const messageBox = document.getElementById('messageBox');
    messageBox.textContent = message;
    messageBox.className = `alert alert-${type} mt-3`;
    messageBox.classList.remove('d-none');
    setTimeout(() => {
        messageBox.classList.add('d-none');
    }, 3000);
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
