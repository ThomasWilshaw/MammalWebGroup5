<?php

	$person_id = $_GET["person_id"];
	include('config.php');
	
	//establish connection
	$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);

	if ($connection->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}

	$uploadArray=array();

	$sql="SELECT * FROM upload WHERE person_id=".$person_id.";";
	$uploadQuery=$connection->query($sql);
	if($uploadQuery->num_rows>0){
		while($row=$uploadQuery->fetch_assoc()){
			$uploadArray[$row["upload_id"]]=$row["timestamp"];
		}
	}
	echo json_encode($uploadArray);
?>