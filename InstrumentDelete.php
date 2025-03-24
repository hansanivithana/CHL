<?php
// Include the sendRequest function
require_once 'sendRequest.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $instrumentID = $_POST['instrumentID'];

    // Make DELETE request to the API
    $response = sendRequest("DELETE", "https://localhost:7150/api/Instruments/RemoveInstrument/$instrumentID");

    // Check the response for success
    if ($response && $response['statusMessage'] == "Instrument removed successfully") {
        echo "<script>alert('Instrument deleted successfully'); window.location.href = 'Instrument.php';</script>";
    } else {
        echo "<script>alert('Failed to delete the instrument'); window.location.href = 'Instrument.php';</script>";
    }
}
?>
