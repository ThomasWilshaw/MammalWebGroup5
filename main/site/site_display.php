<html>
<head>
	<title>Php x SQL</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	<?php 

	/*At the moment, three searches are hard coded into the page. Once we know 
    *the input format from the search form, change to only output a single table 
    *that includes all the fields being searched for.
    */

		set_time_limit(120);
		//sets a 2 minute timeout 
		
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connections
		
		//removes any sql injection from $_REQUEST array
		makeSecureForSQL($connection);

		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);		
		
		$description="none";//will contain an english description of filter criteria specified
		
		/* NB the searching in site_display is a bit different to image_display.
		site_display finds a list of unique site IDs fitting the criteria
		and then does another query using these IDs to get information from the site table
		*/
		if(isset($_REQUEST)){
			$sqlArray=arrayToQuery($_REQUEST,$speciesMap);
			$sql=$sqlArray[0];
			$description=$sqlArray[1];
		}
		$sitesList=$connection->query($sql);
		//sitesList here will be a list of unique site IDs fitting search criteria
		/*constructs a second query to search for all data from the site table
		for the site IDs in the sqlResults array. */
		
		$sql="SELECT * FROM site WHERE site_id =";
		$counter=0;
		if(isset($sitesList->num_rows) && $sitesList->num_rows>0){ 
			while($row=$sitesList->fetch_assoc()){
				    $arrayItem=$row["site_id"];
					if(!empty($arrayItem))
					{
						if($counter==0){
							$sql=$sql.$arrayItem;
						}
						
						else{
							$sql=$sql." OR site_id=".$arrayItem;
						}
						$counter+=1;
					}		
			}
		}
		else{
			echo'no sites found';
		}
		$sql=$sql.";";

		//words and titles on the page shown to viewer other than the table
		echo "<h1>Query Results:</h1><br/>";
		echo'<div class= "row">';
			echo '<div class="col-sm-6">';
				echo '<p> Site filters applied:<br/> '.$description.' <br/></p>';
				echo '<p>'.$counter.' results found. </p>';
				echo '<p> Table showing sites meeting the filter criteria. Use the "back" button to revise the filter. </p>';
			echo '</div>';
			echo '<div class="col-sm-3">';
				
			echo '</div>';
			echo '<div class="col-sm-2">';
				echo'<a href="dropdowns_sites.php" class="btn btn-info btn-lg btn-block" role="button">Back to filter selection</a>';
				echo'<br/>';
				echo'<a href="exportCSV.php?data='.$sql.'" class="btn btn-primary btn-lg btn-block" role="button">Download results</a>';
			echo '</div>';
		echo'</div>';
		/*output table showing sites meeting the filter criteria */
		echo '<table class="table table-hover">';
		echo '<thead>';
		echo "<tr>";
		echo '<th>Site ID</th>';
		echo '<th>Site name</th>';
		echo '<th>Person ID</th>';
		echo '<th>Grid reference</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		
		//TABLE 1 - output results
		$sqlResults=$connection->query($sql);
		if(isset($sqlResults->num_rows) && $sqlResults->num_rows>0){ 
			while($row=$sqlResults->fetch_assoc()){
					$lat=$row["latitude"];
					$long=$row["longitude"];
					//if there is no data on this row for lat/long
					if(!isset($lat)){
						echo "<tr>";
						echo "<td>".$row["site_id"]."</td>";
						echo "<td>".$row["site_name"]."</td>";
						echo "<td>".$row["person_id"]."</td>";
						echo "<td>No Data</td>";
						echo "</tr>";						
					}
					else{
					//if there is data on this row for lat/long
						echo "<tr>";
						echo "<td>".$row["site_id"]."</td>";
						echo "<td>".$row["site_name"]."</td>";
						echo "<td>".$row["person_id"]."</td>";
						echo "<td><a href=\"javascript:;\" src=\"\" onclick=\"popUp(".$lat.",".$long.")\"> ".$row["grid_ref"]." </a></td>";
						echo "</tr>";
					}
			}
		}
		else{
			echo "<tr><td>No sites found</td></tr>";
		}
		echo '</tbody>';
		echo "</table>";

		
		///////////////////////////////////////////////////////////////////////////////////////////////////
		function loadSpeciesMap($connection){
			$sql="SELECT option_id,option_name FROM options";  
			$speciesquery=$connection->query($sql);

			$speciesmap=array();
			while($row=$speciesquery->fetch_assoc()){
				$speciesmap[$row["option_id"]]=$row["option_name"];
			}
			$speciesmap[0]="Undefined";
			return $speciesmap;
		}
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		/*generates an sql query using the variables in $_REQUEST. species is handled as a special
		case at the end since it is the only attribute that uses the aggregate table, and an extra
		join to the aggregate table would slow down the process hugely also generates an english description
		of the filter criteria. Returns an array where index 0 contains the query, index 1 contains the description*/
		function arrayToQuery($inputArray,$speciesMap){
			
			$query="SELECT DISTINCT site.site_id FROM site LEFT JOIN photo on site.site_id=photo.site_id";
			$description="";//the list of filter criteria
			
			$counter=0;
			//counter detects when you are at the start of creating the sql query (for writing select where etc)
			
			$handledGroup1=['person_id','contains_human','site_id','habitat_id'];
			//the group of variables to be handled togethor by the main body of the sql creation code below
			
			$handledGroup2=['time1','time2'];
			//the group of variables to be handled in the time section
			
			$handledGroup3=['lat1','lat2'];
			//latitude and longitude boundaries
			$latDone=false;
			
			$handledGroup4=['long1','long2'];
			//latitude and longitude boundaries
			$longDone=false;
			
			$handledGroup5=['photoCount1','photoCount2'];
			//number of photos taken at a site
			$photoCountDone=false;
			
			$handledGroup6=['sequenceCount1','sequenceCount2'];
			//number of sequences associatd with a site
			$sequenceCountDone=false;
			
			$timeVariablesRecieved=0;
			//used to count the number of time variables recieved,
			//since two must be recieved before the time part of the query can be constructed
			//(before and after)
			
			$num_classVariablesRecieved=0;
			//used to count the number of classification variables recieved
			
			//some attributes are in the site table, other are in the photo table
			$siteAttributes=['site_id','habitat_id'];
			$photoAttributes=['person_id','contains_human'];
			
			foreach($inputArray as $key => $value){
				
				if(in_array($key,$handledGroup1)){//if this is a variable on the list to be handled here
					
					if(!(is_array($value))){
						$rawValue=$value;
						if(empty($rawValue)){
							$rawValue=$value;
						}
						//if there's no information in the species map about this variable
						
						if($rawValue=="any"){
							$rawValue="";
						}
						//values such as "any" that shouldn't influence the query

						
						if((!($rawValue=="")) AND (!($rawValue=="any")))
						{
							/*changes attribute to incude table e.g.
							site.site_id or photo.person_id */
							$modifiedKey=$key;
							if(in_array($modifiedKey,$siteAttributes)){
								$modifiedKey="site.".$key;
							}
							else{
								if(in_array($modifiedKey,$photoAttributes)){
									$modifiedKey="photo.".$key;	
								}									
							}
							
							
							if($counter==0){
								$query=$query." WHERE ".$modifiedKey." = ".$rawValue;
								$description=$description.$key." = ".$rawValue;
							}
							
							else{
								$query=$query." AND ".$modifiedKey." = ".$rawValue;
								$description=$description.",".$key." = ".$rawValue;
							}
							
							$counter=$counter+1;
						}
					}
					
					else{
						if(!in_array("any",$value)){
							//if the "any" option is selected, this overrides other options
														
							/*changes attribute to incude table e.g.
							site.site_id or photo.person_id */
							$modifiedKey=$key;
							if(in_array($modifiedKey,$siteAttributes)){
								$modifiedKey="site.".$key;
							}
							else{
								if(in_array($modifiedKey,$photoAttributes)){
									$modifiedKey="photo.".$key;	
								}									
							}
							if($counter==0){
									$query=$query." WHERE ".$modifiedKey." = ";
									$description=$description.$key." = ";
								}
								
							else{
									$query=$query." AND ".$modifiedKey." = ";
									$description=$description.",".$key." = ";
								}
							$counter=$counter+1;
							$innerCounter=0;
							foreach($value as $arrayItem){
								if($arrayItem=="any"){
									$arrayItem="";
								}
								if(!empty($arrayItem))
								{
									if($innerCounter==0){
										$query=$query.$arrayItem;
										$description=$description.$arrayItem;
									}
									
									else{
										$query=$query." OR ".$arrayItem;
										$description=$description." or ".$arrayItem;
									}
									$innerCounter+=1;
								}	
									
							}
						}
							
					}
					
				}
			
				else{//handling for special variables such as time variables
					
					//if the variable is time1 or time2
					if(in_array($key,$handledGroup2) AND (!empty($value))){
						
						$timeVariablesRecieved+=1;
						
						if($timeVariablesRecieved==2){//must have 
						//before and after time before the time part of the
						//query can be constructed
						
							if($counter==0){
								$query=$query." WHERE ";
								}
										
							else{
								$query=$query." AND ";
								$description=$description.",";
								}
							$counter=$counter+1;
							
							$modifiedStartTime=$_REQUEST['time1'];
							$modifiedEndTime=$_REQUEST['time2'];
							//start and end time must be modified to take the "T" out of the middle of the string
							//to make it work with the sql format for date and time
							$modifiedStartTime="'".str_ireplace("T"," ",$modifiedStartTime)."'";
							$modifiedEndTime="'".str_ireplace("T"," ",$modifiedEndTime)."'";
							$query=$query." taken BETWEEN ".$modifiedStartTime.' AND '.$modifiedEndTime." OR taken BETWEEN ".$modifiedEndTime.' AND '.$modifiedStartTime;
							$description=$description."time of photos taken between ".$modifiedStartTime." and ".$modifiedEndTime;
						}
					}

					//if the variable is in the third behaviour group
					//relating to latitude boundaries
					else if(in_array($key,$handledGroup3) AND (!empty($value)) AND (!$latDone)){
						if($counter==0){
							$query=$query." WHERE ";
							}
									
						else{
							$query=$query." AND ";
							$description=$description.",";
							}
							$counter=$counter+1;
						
						if(!empty($_REQUEST['lat1'])){
							$lat1=$_REQUEST['lat1'];
						}
						else{
							$lat1="-200";
						}
						if(!empty($_REQUEST['lat2'])){
							$lat2=$_REQUEST['lat2'];
						}
						else{
							$lat2="200";
						}
						if($lat1<=$lat2){
							$latLower=$lat1;
							$latHigher=$lat2;
						}
						else{
							$latHigher=$lat1;
							$latLower=$lat2;
						}
						$query=$query." latitude BETWEEN ".$latLower.' AND '.$latHigher;
						$description=$description."latitude between ".$latLower." and ".$latHigher;
						
						$latDone=true;
					}
					
					//if the variable is in the fourth behaviour group
					//relating to longitude
					 else if(in_array($key,$handledGroup4) AND (!empty($value)) AND (!$longDone)){
						
						if($counter==0){
							$query=$query." WHERE ";
							}
									
						else{
							$query=$query." AND ";
							$description=$description.",";
							}
							$counter=$counter+1;
						
						if(!empty($_REQUEST['long1'])){
							$long1=$_REQUEST['long1'];
						}
						else{
							$long1="-200";
						}
						if(!empty($_REQUEST['long2'])){
							$long2=$_REQUEST['long2'];
						}
						else{
							$long2="200";
						}
						if($long1<=$long2){
							$longLower=$long1;
							$longHigher=$long2;
						}
						else{
							$longHigher=$long1;
							$longLower=$long2;
						}
						$query=$query." longitude BETWEEN ".$longLower.' AND '.$longHigher;
						$description=$description."longitude between ".$longLower." and ".$longHigher;
						
						$longDone=true;
					}
					
					
					
					
					//if the variable is in the fifth behaviour group
					//relating to photo count at a site
					else if(in_array($key,$handledGroup5) AND (!empty($value)) AND (!$longDone)){
						
						if($counter==0){
							$query=$query." WHERE ";
							}
									
						else{
							$query=$query." AND ";
							$description=$description.",";
							}
							$counter=$counter+1;
						
						if(!empty($_REQUEST['photoCount1'])){
							$photoCount1=$_REQUEST['photoCount1'];
						}
						else{
							$photoCount1="0";
						}
						if(!empty($_REQUEST['photoCount2'])){
							$photoCount2=$_REQUEST['photoCount2'];
						}
						else{
							$photoCount2="999";
						}
						//makes sure that the first of the pair is lower for SQL between
						if($photoCount1<=$photoCount2){
							$photoCountLower=$photoCount1;
							$photoCountHigher=$photoCount2;
						}
						else{
							$photoCountHigher=$photoCount1;
							$photoCountLower=$photoCount2;
						}
						$query=$query."site.site_id IN (SELECT site.site_id FROM site INNER JOIN photo on site.site_id = photo.site_id GROUP BY site.site_id HAVING COUNT(site.site_id) BETWEEN ".$photoCountLower.' AND '.$photoCountHigher.')';
						$description=$description."between ".$photoCountLower." and ".$photoCountHigher." photos taken";
						$photoCountDone=true;
					}
					
					
					//if the variable is in the sixth behaviour group
					//relating to sequence count at a site
					else if(in_array($key,$handledGroup6) AND (!empty($value)) AND (!$longDone)){
						
						if($counter==0){
							$query=$query." WHERE ";
							}
									
						else{
							$query=$query." AND ";
							$description=$description.",";
							}
							$counter=$counter+1;
						
						if(!empty($_REQUEST['sequenceCount1'])){
							$sequenceCount1=$_REQUEST['sequenceCount1'];
						}
						else{
							$sequenceCount1="0";
						}
						if(!empty($_REQUEST['sequenceCount2'])){
							$sequenceCount2=$_REQUEST['sequenceCount2'];
						}
						else{
							$sequenceCount2="999";
						}
						//makes sure that the first of the pair is lower for SQL between
						if($sequenceCount1<=$sequenceCount2){
							$sequenceCountLower=$sequenceCount1;
							$sequenceCountHigher=$sequenceCount2;
						}
						else{
							$sequenceCountHigher=$sequenceCount1;
							$sequenceCountLower=$sequenceCount2;
						}
						$query=$query."site.site_id IN (SELECT upload.site_id FROM photosequence INNER JOIN upload on upload.upload_id = photosequence.upload_id GROUP BY upload.site_id HAVING COUNT(upload.site_id) BETWEEN ".$sequenceCountLower." and ".$sequenceCountHigher.")";
						$description=$description."between ".$sequenceCountLower." and ".$sequenceCountHigher." sequences taken";
						$sequenceCountDone=true;
					}
				}
			}
			
			$speciesQueried=false;
			//true if the aggregate table must be used for species information
			$speciesQuery="SELECT DISTINCT photo.site_id FROM photo INNER JOIN aggregate on photo.photo_ID = aggregate.photo_ID WHERE photo.site_id IN (".$query.") AND aggregate.species=";
			$speciesDescription="species present: ";
			if(!empty($_REQUEST['species'])){
				$speciesQueried=true;
				//handing changes to query if species was queried
				$innerCounter=0;
				foreach($_REQUEST['species'] as $arrayItem){
					if($arrayItem=="any"){
						$speciesQueried=false;
					}
						if(!empty($arrayItem))
							{
							if($counter!=0){
									$description=$description.",";
							}
							$counter+=1;
							if($innerCounter==0){
								$speciesQuery=$speciesQuery.$arrayItem;
								$rawValue=$speciesMap[$arrayItem];
								$speciesDescription=$speciesDescription.$rawValue;
							}
							
							else{
								$speciesQuery=$speciesQuery." OR ".$arrayItem;
								$rawValue=$speciesMap[$arrayItem];
								$speciesDescription=$speciesDescription.",".$rawValue;
							}
							$innerCounter+=1;
						}	
									
					}
			}
			if($speciesQueried){
				$query=$speciesQuery;
				$description=$description.$speciesDescription;
			}
			
			if(empty($description)){
				$description="none";
			}
			
			$query=$query.";";
			$results=array();
			$results[0]=$query;
			$results[1]=$description;
			return $results;	
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////

		function searchBetweenDates($connection,$d1,$d2){
			$sql="SELECT photo_id FROM photo WHERE taken BETWEEN '".$d1."' AND '".$d2;
			$datequery=$connection->query($sql);

			echo "<h2>Photo IDs from between dates ".$d1." and ".$d2." (current time) maybe want to get filename/some other field in future?";
			echo "<table>";
			echo "<tr><td>Photo ID</td></tr>";
			if($datequery->num_rows>0){
			while($row=$datequery->fetch_assoc()){
				echo"<tr>";
				echo "<td>".$row["photo_id"]."</td>";
				echo"</tr>";
			}
			echo "</table>";
		}
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////
				/////////////////////////////////////////////////////////////////////////////////////////////////////
		/*this function will be used to make sure every variable set in the $_REQUEST array doesn't contain
		injected SQL. It doesn't need to operate on the keys of the array, only the values, as only specific
		keys will be used in the arrayToQuery function to generate the SQL query.
		The $connection parameter is included because the mysqli real_escape_string() function takes this
		as a parameter to see which characters are acceptable*/
		function makeSecureForSQL($myConnection){
			//parses through everything in $_REQUEST
			foreach($_REQUEST as $key=>$value)
			{
				if(is_array($value)){
					foreach($value as $valueKey=>$valueValue)
					{
						//escapes any character that may enable an sql injection attack
						$value[$valueKey]=$myConnection->real_escape_string($valueValue);
					}
				}
				else{
					//escapes any character that may enable an sql injection attack
					$_REQUEST[$key]=$myConnection->real_escape_string($value);
				}
			}
		}
		?>
    
    <!-- Creates the bootstrap modal where the image will appear -->
    <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel">Site location</h4>
          </div>
          <div class="modal-body">
		  
			<iframe id="imagepreview"
		    width="540"
		    height="450"
			frameborder="0" style="border:0"
			src="" 
			allowfullscreen>
			</iframe>
		
          </div>
          <div class="modal-footer">
            <button id="fullScreenButton" type="button" class="btn btn-default" onclick="">Full Screen</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    
    <script>
        function popUp(latitude,longitude){
			var apiKey="AIzaSyC3bN3ZwaXsZ2Eloq_4KOn2CQrXcvL6fIo";//google maps api key (static browser key)
			var embedURL ="https://www.google.com/maps/embed/v1/place?key="+apiKey+"&q="+latitude+","+longitude+"&maptype=satellite";
			var visitURL="https://maps.google.com/?t=h&q="+latitude+","+longitude+"&ll="+latitude+","+longitude+"&z=8"
            console.log("grid reference popup");
            //$('#imagepreview').attr('src', embedURL); // here asign the embed url for google maps
			var visitFunction = "window.location='"+visitURL+"'";//the location the 'fullscreen button takes you to
			$('#imagepreview').attr('src',embedURL);
			$('#fullScreenButton').attr('onClick',visitFunction);// here assign the visit url for google maps
            $('#imagemodal').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function
        }
        
    </script>
</body>
</html>