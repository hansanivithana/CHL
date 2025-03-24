<?php

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    echo "<script>alert('You must be logged in.'); window.location.href = 'login.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$sellerID = $user['userID'];
$role = $user['role'];

// Disable SSL verification for localhost
$contextOptions = array(
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
);

// Fetch instruments based on user role
function getInstruments($role, $sellerID)
{
    $url = ($role === 'Admin') ? 
        "https://localhost:7150/api/Instruments/GetInstruments" : 
        "https://localhost:7150/api/Instruments/GetInstrumentsBySeller/$sellerID";
    
    $response = file_get_contents($url, false, stream_context_create($GLOBALS['contextOptions']));
    return json_decode($response, true)['data'] ?? [];
}

// Update instrument
if (isset($_POST['update'])) {
    $id = $_POST['instrumentID'];
    $newName = $_POST['instrumentName'];
    $newDesc = $_POST['description'];
    $newCondition = $_POST['condition'];
    $newPrice = $_POST['price'];
    
    // Handle image upload
    $newImage = $_POST['currentPicture']; // Default to current image
    if (!empty($_FILES['instrumentPicture']['name'])) {
        $newImage = "Uploads/" . basename($_FILES['instrumentPicture']['name']);
        move_uploaded_file($_FILES['instrumentPicture']['tmp_name'], $newImage);
    }

    $data = [
        "instrumentID" => (int)$id,
        "instrumentName" => $newName,
        "description" => $newDesc,
        "condition" => $newCondition,
        "price" => (int)$newPrice,
        "sellerID" => (int)$sellerID,
        "instrumentPicture" => $newImage,
        "dateAdded" => date("Y-m-d\TH:i:s")
    ];

    $ch = curl_init("https://localhost:7150/api/Instruments/UpdateInstrument/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "accept: */*"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    curl_close($ch);

    echo "<script>alert('Update Response: $response'); window.location.href = 'InstrumentUpdate.php';</script>";
}

// Delete instrument
if (isset($_POST['delete'])) {
    $id = $_POST['instrumentID'];

    $ch = curl_init("https://localhost:7150/api/Instruments/RemoveInstrument/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["accept: */*"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    curl_close($ch);
    echo "<script>alert('Delete Response: $response'); window.location.href = 'InstrumentUpdate.php';</script>";
}

$instruments = getInstruments($role, $sellerID);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Instruments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('http://localhost/All_FOR_MUSIC/Logo/logo.jpeg') no-repeat center center/cover;
            position: relative;
            color: gold;
            text-align: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: 80px;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: -1;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.9);
            padding: 10px 20px;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.5);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .content-container {
            margin-top: 100px; /* Fix for navbar overlap */
        }

        .table-responsive {
            overflow-x: auto; /* Ensures table doesn't crash on small screens */
            border-radius: 10px;
        }

        .image-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid gold;
        }

        .btn {
            border-radius: 5px;
        }

        .modal-content {
            border-radius: 12px;
        }

        .modal-header {
            background: #222;
            color: gold;
            border-radius: 12px 12px 0 0;
        }

        .modal-footer {
            background: #222;
            border-radius: 0 0 12px 12px;
        }

        table th, table td {
            vertical-align: middle;
            text-align: center;
            white-space: nowrap; /* Prevents text from breaking */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <a class="navbar-brand" href="#">
            <img src="http://localhost/All_FOR_MUSIC/Logo/logo.jpeg" width="120">
        </a>
    </nav>

    <div class="container content-container">
        <h2 class="text-warning">Instrument List</h2>
        <div class="table-responsive">
            <table class="table table-dark table-striped">
                <thead class="table-warning text-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Condition</th>
                        <th>Price</th>
                        <th>Seller</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($instruments as $instrument): ?>
                        <tr>
                            <td><?= $instrument['instrumentID'] ?></td>
                            <td><?= $instrument['instrumentName'] ?></td>
                            <td><?= $instrument['description'] ?></td>
                            <td><?= $instrument['condition'] ?></td>
                            <td>$<?= number_format($instrument['price'], 2) ?></td>
                            <td><?= $instrument['sellerID'] ?></td>
                            <td><img src="<?= $instrument['instrumentPicture'] ?>" class="image-preview"></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $instrument['instrumentID'] ?>">Edit</button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $instrument['instrumentID'] ?>">Delete</button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $instrument['instrumentID'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Instrument</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="instrumentID" value="<?= $instrument['instrumentID'] ?>">
                                            <label>Name:</label>
                                            <input type="text" class="form-control" name="instrumentName" value="<?= $instrument['instrumentName'] ?>" required>
                                            <label>Description:</label>
                                            <input type="text" class="form-control" name="description" value="<?= $instrument['description'] ?>" required>
                                            <label>Condition:</label>
                                            <input type="text" class="form-control" name="condition" value="<?= $instrument['condition'] ?>" required>
                                            <label>Price:</label>
                                            <input type="number" class="form-control" name="price" value="<?= $instrument['price'] ?>" required>
                                            <label>Seller ID:</label>
                                            <input type="number" class="form-control" name="sellerID" value="<?= $instrument['sellerID'] ?>" required>
                                            <label>Current Image:</label><br>
                                            <img src="<?= $instrument['instrumentPicture'] ?>" class="image-preview mb-2"><br>
                                            <label>New Image:</label>
                                            <input type="file" class="form-control" name="instrumentPicture">
                                            <div class="modal-footer">
                                                <button type="submit" name="update" class="btn btn-warning">Update</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?= $instrument['instrumentID'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content bg-dark text-light">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Instrument</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete <strong><?= $instrument['instrumentName'] ?></strong>?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <form method="post">
                                            <input type="hidden" name="instrumentID" value="<?= $instrument['instrumentID'] ?>">
                                            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

