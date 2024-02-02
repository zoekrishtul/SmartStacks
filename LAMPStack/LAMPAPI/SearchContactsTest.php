<?php
	// Changed Name to firstName everywhere i found it
	// Did not change color text
	$inData = getRequestInfo();
	
	$searchResults = "";
	$searchCount = 0;

	if (empty($inData["userId"]))
	{
		returnWithError("Incorrect JSON Format: Missing userId field");
	}

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		// $stmt = $conn->prepare("select * from Contacts where firstName like ? and UserID=?"); THIS ONE WORKS
		$stmt = $conn->prepare("select * from Contacts where UserID=? AND (firstName LIKE ? 
  			OR lastName LIKE ? 
     			OR Phone LIKE ?
			OR Email LIKE ?)");
		$search = "%" . $inData["search"] . "%";
		$stmt->bind_param("sssss", $inData["userId"], $search, $search, $search, $search);
		$stmt->execute();
		
		$result = $stmt->get_result();
		
		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			// $searchResults .= '"' . $row["firstName"] . '"'; WORKS
			// Major change here
			$searchResults .= '{"firstName" : "'. $row["firstName"] .'", 
				"lastName" : "'. $row["lastName"] .'",
				"Phone" : "'. $row["Phone"] .'",
				"Email" : "'. $row["Email"] .'",
    				"ID" : "'. $row["ID"] .'"}';
		}
		
		if( $searchCount == 0 && !(empty($inData["userId"])))
		{
			returnWithError( "No Records Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
		}
		
		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"ID":"0","firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
