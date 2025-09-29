<?php
// api/Login.php
header("Content-Type: application/json");

$inData = getRequestInfo();

$login = trim($inData["login"] ?? "");
$password = trim($inData["password"] ?? "");

if (!$login || !$password) {
    returnWithError("Enter login and password.");
    exit();
}

$conn = new mysqli("localhost", "appuser", "AppPass123!", "COP4331");


if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare(
        "SELECT ID, FirstName, LastName, Password FROM Users WHERE Login=? LIMIT 1"
    );
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["Password"])) {
            returnWithInfo($row["FirstName"], $row["LastName"], $row["ID"]);
        } else {
            returnWithError("Invalid password.");
        }
    } else {
        returnWithError("No user found with that login.");
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

function returnWithInfo($firstName, $lastName, $id)
{
    sendResultInfoAsJson([
        "id" => $id,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "error" => ""
    ]);
}
?>
