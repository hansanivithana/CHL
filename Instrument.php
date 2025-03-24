<?php

session_start();

// Ensure the user is logged in and has the correct role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Admin', 'Instructor', 'Musician', 'Artist'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$dateCreated = isset($user['dateCreated']) ? preg_replace('/\.\d{3}$/', '', $user['dateCreated']) : '';
$formattedDate = !empty($dateCreated) ? (new DateTime($dateCreated))->format('F j, Y, g:i a') : 'Unknown Date';
$profilePictureURL = $user['profilePicture'] ?? 'default_profile.jpg';

// Store user info in session
$_SESSION['user'] = [
    'userID' => $user['userID'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'profilePicture' => $profilePictureURL,
    'dateCreated' => $user['dateCreated']
];

$upload_dir = __DIR__ . "/Uploads/Profile_Picture/";
$default_image = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/default.jpg";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function sendRequest($method, $url, $data = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }
    curl_close($ch);
    return json_decode($result, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == "add" || $_POST['action'] == "update") {
        $imagePath = $default_image;
        if (!empty($_FILES['instrumentPicture']['name'])) {
            $filePath = $upload_dir . basename($_FILES['instrumentPicture']['name']);
            if (move_uploaded_file($_FILES['instrumentPicture']['tmp_name'], $filePath)) {
                $imagePath = "http://localhost/All_FOR_MUSIC/Uploads/Profile_Picture/" . basename($_FILES['instrumentPicture']['name']);
            }
        }

        $data = [
            "instrumentName" => $_POST['instrumentName'],
            "description" => $_POST['description'],
            "condition" => $_POST['condition'],
            "price" => $_POST['price'],
            "sellerID" => $_POST['sellerID'],
            "instrumentPicture" => $imagePath
        ];

        if ($_POST['action'] == "add") {
            sendRequest("POST", "https://localhost:7150/api/Instruments/AddInstrument", $data);
        } elseif ($_POST['action'] == "update") {
            $data["instrumentID"] = $_POST['instrumentID'];
            sendRequest("PUT", "https://localhost:7150/api/Instruments/UpdateInstrument", $data);
        }
    } elseif ($_POST['action'] == "delete") {
        sendRequest("DELETE", "https://localhost:7150/api/Instruments/DeleteInstrument/" . $_POST['instrumentID']);
    }
}

// Fetch instruments based on role
if ($user['role'] === 'Admin') {
    $response = sendRequest("GET", "https://localhost:7150/api/Instruments/GetInstruments");
} else {
    $response = sendRequest("GET", "https://localhost:7150/api/Instruments/GetInstrumentsBySeller/" . $user['userID']);
}

$instruments = $response && isset($response['data']) ? $response['data'] : [];

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instrument Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-image: url("http://localhost/All_FOR_MUSIC/Logo/logo.jpeg");
            background-size: cover;
            background-position: center;
            backdrop-filter: blur(10px);
            color: gold;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: black;
            padding: 10px;
        }
        .navbar-brand img {
            width: 50px;
            height: auto;
        }
        .container {
            max-width: 900px;
            background: rgba(0, 0, 0, 0.85);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(255, 215, 0, 0.6);
            transition: transform 0.3s ease-in-out;
        }
        .container:hover {
            transform: scale(1.03);
        }
        .table {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            color: gold;
            border-color: gold;
            background: rgba(20, 20, 20, 0.8); /* Transparent black-gray color */
        }
        .modal-content {
            background: rgba(0, 0, 0, 0.9);
            color: gold;
        }
        .modal-header {
            background-color: gold;
            color: black;
        }
        .btn {
            transition: all 0.3s ease;
            background-color: gold;
            color: black;
            border: none;
        }
        .btn:hover {
            background-color: black;
            color: gold;
            border: 1px solid gold;
        }
        #imagePreview {
            display: none;
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border: 2px solid gold;
            padding: 5px;
            border-radius: 8px;
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
    
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Instrument Management</h2>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Instrument</button>
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Name</th><th>Description</th><th>Condition</th><th>Price</th><th>Seller ID</th><th>Picture</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instruments as $instrument): ?>
                    <tr>
                        <td><?= htmlspecialchars($instrument['instrumentName']) ?></td>
                        <td><?= htmlspecialchars($instrument['description']) ?></td>
                        <td><?= htmlspecialchars($instrument['condition']) ?></td>
                        <td>Rs<?= htmlspecialchars($instrument['price']) ?></td>
                        <td><?= htmlspecialchars($instrument['sellerID']) ?></td>
                        <td><img src="<?= htmlspecialchars($instrument['instrumentPicture'] ?? $default_image) ?>" width="80" height="80" class="rounded"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Instrument</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" enctype="multipart/form-data">
                        <input type="text" name="instrumentName" placeholder="Instrument Name" required class="form-control mb-2">
                        <textarea name="description" placeholder="Description" class="form-control mb-2"></textarea>
                        <input type="text" name="condition" placeholder="Condition" required class="form-control mb-2">
                        <input type="number" name="price" placeholder="Price" required class="form-control mb-2">

                        <input type="text" name="sellerID" placeholder="Seller ID" required class="form-control mb-2" required value="<?php echo isset($_SESSION['user']['userID']) ? $_SESSION['user']['userID'] : ''; ?>">

                        <input type="file" name="instrumentPicture" id="instrumentPicture" class="form-control mb-2">
                        <img id="imagePreview" class="rounded">
                        <button type="submit" class="btn w-100">Add Instrument</button>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $('#instrumentPicture').change(function() {
                let input = this;
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });

            $('#addForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append('action', 'add');
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>
