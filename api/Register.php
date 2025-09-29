<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// api/Register.php
header("Content-Type: application/json");

$inData = getRequestInfo();

$firstName = trim($inData["firstName"] ?? "");
$lastName  = trim($inData["lastName"] ?? "");
$login     = trim($inData["login"] ?? "");
$password  = trim($inData["password"] ?? "");

// Input validation
if (!$firstName || !$lastName || !$login || !$password) {
    returnWithError("All fields are required.");
    exit();
}

$conn = new mysqli("localhost", "appuser", "AppPass123!", "COP4331");


if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    // Hash password securely before storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $firstName, $lastName, $login, $hashedPassword);

    if ($stmt->execute()) {
        $id = $conn->insert_id;
        returnWithInfo($firstName, $lastName, $id);
    } else {
        if ($conn->errno === 1062) {
            returnWithError("Login already exists.");
        } else {
            returnWithError("Database error: " . $conn->error);
        }
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
