<?php
	//$person_id = $_GET["person_id"];
	include('config.php');
	$person_id=204;
	//establish connection
	$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);

	if ($connection->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
	}

	$likeOption=-1;
	$sql="SELECT option_id FROM options WHERE option_name='Like';";
	$likeOptionQuery=$connection->query($sql);
	if($likeOptionQuery->num_rows>0){
		$row=$likeOptionQuery->fetch_assoc();
		$likeOption=$row["option_id"];
	}
	$idArray=array();
	$sql="SELECT photo_id from animal WHERE person_id=".$person_id." AND species=".$likeOption.";";
	$likedPhotoQuery=$connection->query($sql);
	if($likedPhotoQuery->num_rows>0){
		while($row=$likedPhotoQuery->fetch_assoc()){
			$idArray[]=$row["photo_id"];
		}
	}

	//Construct query with from array of photo_ids
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
?>