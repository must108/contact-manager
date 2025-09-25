<?php

$inData = getRequestInfo();

$firstName = $inData["firstName"] ?? "";
$lastName  = $inData["lastName"] ?? "";
$phoneNum  = $inData["phoneNum"] ?? "";
$email     = $inData["email"] ?? "";

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    // Check if contact email already exists
    $stmt = $conn->prepare("SELECT ID FROM Contacts WHERE Email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        returnWithError("Contact already exists with this email");
    } else {
        // Insert new contact
        $stmt = $conn->prepare("INSERT INTO Contacts (FirstName, LastName, PhoneNum, Email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $phoneNum, $email);

        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            returnWithInfo($firstName, $lastName, $phoneNum, $email, $newId);
        } else {
            returnWithError("Error creating contact");
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
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"id":0,"firstName":"","lastName":"","phoneNum":"","email":"","error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $phoneNum, $email, $id)
{
    $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","phoneNum":"' . $phoneNum . '","email":"' . $email . '","error":""}';
    sendResultInfoAsJson($retValue);
}

?>
