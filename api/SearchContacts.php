<?php
// api/SearchContacts.php
header("Content-Type: application/json");

$inData = getRequestInfo();

$userId = intval($inData["userId"] ?? 0);
$search = "%" . trim($inData["search"] ?? "") . "%";

if (!$userId) {
    returnWithError("Missing userId.");
    exit();
}

$conn = new mysqli("localhost", "appuser", "AppPass123!", "COP4331");


if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare(
        "SELECT ID as contactId, FirstName, LastName, Phone, Email, DateCreated
         FROM Contacts
         WHERE UserID=? AND 
               (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?)
         ORDER BY DateCreated DESC"
    );
    $stmt->bind_param("issss", $userId, $search, $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    $contacts = [];
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }

    sendResultInfoAsJson(["results" => $contacts, "error" => ""]);

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
    sendResultInfoAsJson(["results" => [], "error" => $err]);
}
?>
