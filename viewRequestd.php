<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    echo "<script>alert('You are not authorized to access this page.'); window.location.href = 'loging.php';</script>";
    exit();
}

$user = $_SESSION['user'];
$requestedEntityID = $user['userID']; // RequestedEntityID should match logged-in user ID

function fetchRequests($requestedEntityID) {
    $apiUrl = "https://localhost:7150/api/Request/GetRequestsByRequestedEntityId?requestedEntityID=$requestedEntityID";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function updateRequestStatus($requestID, $status) {
    $apiUrl = "https://localhost:7150/api/Request/UpdateRequestStatus/$requestID";
    
    // Prepare the data as a JSON string
    $data = json_encode($status);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",  // Send PUT request
        CURLOPT_POSTFIELDS => $data,    // Send raw JSON data
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",  // For sending JSON data
            "Accept: */*"  // Accept any response
        ],
        CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL peer verification
        CURLOPT_SSL_VERIFYHOST => false   // Disable SSL host verification
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }
    
    curl_close($ch);
    return json_decode($response, true);
}


// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['requestID']) && isset($_POST['status'])) {
    $requestID = $_POST['requestID'];
    $status = $_POST['status'];
    updateRequestStatus($requestID, $status);
}

$requests = fetchRequests($requestedEntityID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>My Requests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Request Type</th>
                    <th>Requester ID</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests['data'])): ?>
                    <?php foreach ($requests['data'] as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['requestID']); ?></td>
                            <td><?php echo htmlspecialchars($request['requestType']); ?></td>
                            <td><?php echo htmlspecialchars($request['requesterID']); ?></td>
                            <td><?php echo htmlspecialchars($request['status']); ?></td>
                            <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($request['dateRequested']))); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="requestID" value="<?php echo $request['requestID']; ?>">
                                    <select name="status" class="form-select">
                                        <option value="Pending" <?php echo ($request['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Accepted" <?php echo ($request['status'] === 'Accepted') ? 'selected' : ''; ?>>Accepted</option>
                                        <option value="Declined" <?php echo ($request['status'] === 'Declined') ? 'selected' : ''; ?>>Declined</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-2">Update Status</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
