<?php
// api/UpdateContact.php
header("Content-Type: application/json");

$inData = getRequestInfo();

$userId    = $inData["userId"] ?? 0;
$contactId = $inData["contactId"] ?? 0;
$firstName = trim($inData["firstName"] ?? "");
$lastName  = trim($inData["lastName"] ?? "");
$phone     = trim($inData["phone"] ?? "");
$email     = trim($inData["email"] ?? "");

if (!$userId || !$contactId || !$firstName || !$lastName || !$phone || !$email) {
    returnWithError("All fields required.");
    exit();
}

$conn = new mysqli("localhost", "appuser", "AppPass123!", "COP4331");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare(
        "UPDATE Contacts
         SET FirstName=?, LastName=?, Phone=?, Email=?
         WHERE ID=? AND UserID=?"
    );
    $stmt->bind_param("ssssii", $firstName, $lastName, $phone, $email, $contactId, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        returnWithInfo("Contact updated successfully.");
    } else {
        returnWithError("Update failed or no changes made.");
    }

    $stmt->close();
    $conn->close();
}

function getRequestInfo() {
    return json_decode(file_get_contents("php://input"), true);
}

function sendResultInfoAsJson($obj) {
    echo json_encode($obj);
}

function returnWithError($err) {
    sendResultInfoAsJson(["error" => $err]);
}

function returnWithInfo($msg) {
    sendResultInfoAsJson(["error" => "", "message" => $msg]);
}
?>
