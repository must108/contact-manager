<?php

$inData = getRequestInfo();

$firstName = $inData["firstName"] ?? "";
$lastName  = $inData["lastName"] ?? "";
#$email     = $inData["email"] ?? "";
$username = $inData["username"] ?? ""; #We are using a Username, not an email for registry
$password  = $inData["password"] ?? "";

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    #// Check if email already exists
    #$stmt = $conn->prepare("SELECT ID FROM Users WHERE Email=?");
    #$stmt->bind_param("s", $email);
    //Checks to see if USERNAME EXISTS, we arent using an email for this portion, sorry
    $stmt = $conn->prepare("SELECT ID FROM Users WHERE Username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Email already exists
        returnWithError("Username already registered");
    } else {
        // Insert new user
        #$stmt = $conn->prepare("INSERT INTO Users (firstName, lastName, Email, Password) VALUES (?, ?, ?, ?)");
        #$stmt->bind_param("ssss", $firstName, $lastName, $email, $password);
        $stmt = $conn->prepare("INSERT INTO Users (firstName, lastName, Username, Password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $username, $password);

        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            returnWithInfo($firstName, $lastName, $username, $newId);
        } else {
            returnWithError("Error creating account");
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

// function returnWithError($err)
// {
//     $retValue = '{"id":0,"firstName":"","lastName":"","email":"","error":"' . $err . '"}';
//     sendResultInfoAsJson($retValue);
// }

// function returnWithInfo($firstName, $lastName, $email, $id)
// {
//     $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","email":"' . $email . '","error":""}';
//     sendResultInfoAsJson($retValue);
// }
function returnWithError($err)
{
    $retValue = '{"id":0,"firstName":"","lastName":"","username":"","error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $username, $id)
{
    $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","username":"' . $username . '","error":""}';
    sendResultInfoAsJson($retValue);
}

?>

