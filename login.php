<?php

$inData = getRequestInfo();

$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331"); 	
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	// Check username and password
	$stmt = $conn->prepare("SELECT ID, firstName, lastName FROM Users WHERE Login=? AND Password=?");
	$stmt->bind_param("ss", $inData["login"], $inData["password"]);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($row = $result->fetch_assoc()) {
		// Successful login
		returnWithInfo($row['firstName'], $row['lastName'], $row['ID']);
	} else {
		// Invalid login
		http_response_code(400);
		returnWithError("Invalid login/password");
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
	$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
	sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $id)
{
	$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
	sendResultInfoAsJson($retValue);
}

?>
