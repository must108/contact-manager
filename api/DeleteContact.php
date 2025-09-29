<?php
// api/DeleteContact.php
header("Content-Type: application/json");

$inData = getRequestInfo();

$userId    = intval($inData["userId"] ?? 0);
$contactId = intval($inData["contactId"] ?? 0);

if (!$userId || !$contactId) {
    returnWithError("Missing userId or contactId.");
    exit();
}

$conn = new mysqli("localhost", "appuser", "AppPass123!", "COP4331");


if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare(
        "DELETE FROM Contacts WHERE ID=? AND UserID=?"
    );
    $stmt->bind_param("ii", $contactId, $userId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            returnWithInfo("Contact deleted.");
        } else {
            returnWithError("No contact found.");
        }
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
