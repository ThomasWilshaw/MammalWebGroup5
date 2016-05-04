<?php
/*
Allow access to simple statistics and download of the Mammal Web database (http://www.mammalweb.org/)
Copyright (C) 2016  Freddie Keen, Quentin Lam, Will Taylor, Tom White, 
Thomas Wilshaw
contact:cs-seg5@durham.ac.uk


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
	//$person_id=204;
	//establish connection
	$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);

	if ($connection->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}

	//Get the option_id of the 'like' option
	$likeOption=-1;
	$sql="SELECT option_id FROM options WHERE option_name='Like';";
	$likeOptionQuery=$connection->query($sql);
	if($likeOptionQuery->num_rows>0){
		$row=$likeOptionQuery->fetch_assoc();
		$likeOption=$row["option_id"];
	}

	//Get all photo_ids of photos the user has liked
	$idArray=array();
	$sql="SELECT photo_id from animal WHERE person_id=".$person_id." AND species=".$likeOption.";";
	$likedPhotoQuery=$connection->query($sql);
	if($likedPhotoQuery->num_rows>0){
		while($row=$likedPhotoQuery->fetch_assoc()){
			$idArray[]=$row["photo_id"];
		}
		//Construct query with from array of photo_ids to get all the details needed to construct urls
		$sql="SELECT person_id,site_id,filename FROM photo WHERE ";
		foreach($idArray as $id){
			$sql=$sql."photo_id=".$id." OR ";
		}
		$sql=substr($sql,0,strlen($sql)-4).";";
		
		$urlArray=array();

		$urlPartQuery=$connection->query($sql);
		if($urlPartQuery->num_rows>0){
			while($row=$urlPartQuery->fetch_assoc()){
				$url="http://www.mammalweb.org/biodivimages/person_".$row["person_id"]."/site_".$row["site_id"]."/".$row["filename"];
				$urlArray[]=$url;
			}
		}
		echo json_encode($urlArray);
	}
	else{
		echo 'no_likes';
	}
?>