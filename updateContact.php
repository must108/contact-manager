<?php
$inData = getRequestInfo();

$id       = $inData["id"] ?? 0;
$fullName = $inData["fullName"] ?? "";
$phoneNum = $inData["phoneNum"] ?? "";
$email    = $inData["email"] ?? "";

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $stmt = $conn->prepare("UPDATE Contacts SET FullName=?, PhoneNum=?, Email=? WHERE ID=?");
    $stmt->bind_param("sssi", $fullName, $phoneNum, $email, $id);

    if ($stmt->execute()) {
        returnWithInfo($id, $fullName, $phoneNum, $email);
    } else {
        returnWithError("Error updating contact");
    }

    $stmt->close();
    $conn->close();
}

function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj) {
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err) {
    $retValue = '{"id":0,"fullName":"","phoneNum":"","email":"","error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($id, $fullName, $phoneNum, $email) {
    $retValue = '{"id":' . $id . ',"fullName":"' . $fullName . '","phoneNum":"' . $phoneNum . '","email":"' . $email . '","error":""}';
    sendResultInfoAsJson($retValue);
}
?>
