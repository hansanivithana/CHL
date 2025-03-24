<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$requesterID = $user['userID']; // Auto-fill user ID

function fetchData($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$instructors = fetchData("https://localhost:7150/api/User/GetUsersByRole?role=Instructor");
$artists = fetchData("https://localhost:7150/api/User/GetUsersByRole?role=Artist");
$lessons = fetchData("https://localhost:7150/api/Instruments/GetInstruments");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestType = $_POST['requestType'];
    $requestedEntityID = $_POST['requestedEntityID'];
    $dateRequested = date('Y-m-d\TH:i:s');

    $requestData = [
        "requestID" => 0,
        "requestType" => $requestType,
        "requesterID" => $requesterID,
        "requestedEntityID" => $requestedEntityID,
        "status" => "Pending",
        "dateRequested" => $dateRequested
    ];

    $apiUrl = "https://localhost:7150/api/Request/AddRequest";
    $headers = [
        "Content-Type: application/json",
        "Accept: */*"
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $successMessage = "Request added successfully.";
    } else {
        $errorMessage = "Failed to add request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <script>
    function updateRequestedEntity() {
        let requestType = document.getElementById('requestType').value;
        let requestedEntity = document.getElementById('requestedEntityID');
        requestedEntity.innerHTML = ''; // Clear previous options

        let options = [];

        if (requestType === 'Instructor') {
            options = <?php echo json_encode($instructors['data'] ?? []); ?>;
        } else if (requestType === 'Artist') {
            options = <?php echo json_encode($artists['data'] ?? []); ?>;
        } else if (requestType === 'Lesson') {
            options = <?php echo json_encode($lessons['data'] ?? []); ?>;
        }

        if (options.length === 0) {
            let defaultOption = document.createElement('option');
            defaultOption.textContent = "No records found";
            defaultOption.disabled = true;
            defaultOption.selected = true;
            requestedEntity.appendChild(defaultOption);
        } else {
            options.forEach(item => {
                let option = document.createElement('option');
                option.value = item.userID || item.instrumentID;
                option.textContent = item.username || item.instrumentName;
                requestedEntity.appendChild(option);
            });
        }
    }
</script>


</head>
<body>
    <div class="container mt-5">
        <h2>Add New Request</h2>
        <?php if (isset($successMessage)) { echo "<div class='alert alert-success'>$successMessage</div>"; } ?>
        <?php if (isset($errorMessage)) { echo "<div class='alert alert-danger'>$errorMessage</div>"; } ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Request Type</label>
                <select name="requestType" id="requestType" class="form-control" required onchange="updateRequestedEntity()">
                    <option value="Instructor">Instructor</option>
                    <option value="Lesson">Instrument</option>
                    <option value="Artist">Artist</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Requester ID</label>
                <input type="number" name="requesterID" class="form-control" value="<?php echo $requesterID; ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Requested Entity</label>
                <select name="requestedEntityID" id="requestedEntityID" class="form-control" required>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>
</body>
</html>
