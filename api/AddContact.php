<?php
// api/AddContact.php
header("Content-Type: application/json");

$inData = getRequestInfo();

$userId    = intval($inData["userId"] ?? 0);
$firstName = trim($inData["firstName"] ?? "");
$lastName  = trim($inData["lastName"] ?? "");
$phone     = trim($inData["phone"] ?? "");
$email     = trim($inData["email"] ?? "");

if (!$userId || !$firstName || !$lastName || !$phone || !$email) {
    returnWithError("All fields are required.");
    exit();
}

$conn = new mysqli("localhost", "appuser", "AppPass123!", "COP4331");


if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO Contacts (UserID, FirstName, LastName, Phone, Email) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $userId, $firstName, $lastName, $phone, $email);

    if ($stmt->execute()) {
        returnWithInfo("Contact added.");
    } else {
        returnWithError("Database error: " . $conn->error);
    }

    $stmt->close();
    $conn->close();
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}
function sendResultInfoAsJson($obj)
{
    echo json_encode($obj);
}
function returnWithError($err)
{
    sendResultInfoAsJson(["error" => $err]);
}
function returnWithInfo($msg)
{
    sendResultInfoAsJson(["result" => $msg, "error" => ""]);
}
?>
