<?php
/*
Allow access to simple statistics and download of the Mammal Web database (http://www.mammalweb.org/)
Copyright (C) 2016  Freddie Keen, Quentin Lam, Will Taylor, Tom White, 
Thomas Wilshaw
contact: cs-seg5@durham.ac.uk


This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
	
	$person_id = $_GET["person_id"];
	include('config.php');
	
	//establish connection
	$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);

	if ($connection->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}
	$response=array();
	$uploadArray=array();

	$sql="SELECT * FROM upload WHERE person_id=".$person_id.";";
	$uploadQuery=$connection->query($sql);
	if($uploadQuery->num_rows>0){
		while($row=$uploadQuery->fetch_assoc()){
			$dataArray=array();

			$sql="SELECT * FROM photo WHERE upload_id=".$row["upload_id"];
			$uploadDataQuery=$connection->query($sql);

			$dataArray["num_photos"]=$uploadDataQuery->num_rows;
			$dataArray["timestamp"]=$row["timestamp"];
			$uploadArray[$row["upload_id"]]=$dataArray;
		}
	}
	$response["uploads"]=$uploadArray;

	$animalArray=array();

	$sql="SELECT * FROM animal WHERE person_id=".$person_id.";";
	$animalQuery=$connection->query($sql);
	if($animalQuery->num_rows>0){
		while($row=$animalQuery->fetch_assoc()){
			$animalArray[$row["animal_id"]]=$row["timestamp"];
		}
	}
	$response["classifications"]=$animalArray;
	
	echo json_encode($response);
?>