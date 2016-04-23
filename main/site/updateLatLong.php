<?php
	
	include('config.php');
	include('phpcoord-2.3.php');
		
    //establish connection
     $connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection
		
		
	//checking that a latitude column exists in the site table - if it doesn't, create it
	$latitudeColumn =$connection->query("SELECT latitude FROM site;");
	
	
	if(!$latitudeColumn){
		echo'No latitude column found - creating column. ';
		$creationQuery="ALTER TABLE site ADD latitude Decimal(9,6);";
		$connection->query($creationQuery);
	}
	
	//checing that a longitude column exists in the site table - if it doesn't, create it
	$longitudeColumn =$connection->query("SELECT longitude FROM site;");
	
	if(!$latitudeColumn){
		echo'No longitude column found - creating column. ';
		$creationQuery="ALTER TABLE site ADD longitude Decimal(9,6);";
		$connection->query($creationQuery);
	}
	
	
	//Find which sites in the table have a null latitude and or longitude value
	$selectReferences="SELECT grid_ref from site WHERE latitude IS NULL OR longitude IS NULL;";
	$queryResultsLat=$connection->query($selectReferences);
	$valuesToUpdate=array();//grid refs with a null latitude and or longitude currently
	/*adds to an array grid references recorded in the database which
	have no latitude and or longitude value recorded for them */
	while($row=$queryResultsLat->fetch_assoc()){
		$gridReference=$row['grid_ref'];
		if(!empty($gridReference)){
			$valuesToUpdate[$gridReference]=0;
		}
	}
	
	/* now use the script phpcoord-2.3.php (c) 2005 Jonathan Stott
	and found at http://www.jstott.me.uk/phpcoord/test-2.3.php
	to convert these grid references to latitude and longitude values
	which will then be stored in the database (uses WGS84 datum for lat long*/
	
	foreach ($valuesToUpdate as $key => $val) {
		$gridReference=str_replace(' ','',$key);
		$OSReference = getOSRefFromSixFigureReference($gridReference);
		$LatLong = $OSReference->toLatLng();
		$LatLong->OSGB36ToWGS84();
		$lat= $LatLong->getLat();
		$long= $LatLong->getLong();
		//now update the database with this latitude and longitude 
		$updateQuery="UPDATE site SET latitude=".$lat.",longitude=".$long." WHERE grid_ref='".$key."';";
		echo $updateQuery;
		$connection->query($updateQuery);
		//echo 'Latitude and longitude updated for grid reference: '.$gridReference;
	}
	

	
	

?>