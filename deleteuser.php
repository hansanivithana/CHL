<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid user ID!";
    exit;
}

$userID = intval($_GET['id']); // Convert to integer for security

// API URL
$api_url = "https://localhost:7150/api/User/RemoveUser/$userID";

// Initialize cURL session
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local testing
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json"
]);

// Execute cURL request
$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

// Check if user was successfully removed
if ($http_status === 200 && isset($data['statusMessage']) && $data['statusMessage'] === "User removed successfully.") {
    echo "<script>alert('User removed successfully!'); window.location.href='viewuser.php';</script>";
} else {
    echo "<script>alert('Failed to remove user.'); window.location.href='viewuser.php';</script>";
}
?>
