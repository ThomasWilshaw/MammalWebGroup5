<html>
<head>
	<title>Php x SQL</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	
	<a href="dropdowns_images.php">Back</a>  <!--  MAKE THIS POINT BACK TO THE SEARCH pAGE  -->
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

		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);		
		
		/* NB the searching in site_display is a bit different to image_display.
		site_display finds a list of unique site IDs fitting the criteria
		and then does another query using these IDs to get information from the site table
		*/
		if(isset($_REQUEST)){
			$sql=arrayToQuery($_REQUEST,$speciesMap);
		}
		
		$sitesList=$connection->query($sql);
		//sitesList here will be a list of unique site IDs fitting search criteria
		/*constructs a second query to search for all data from the site table
		for the site IDs in the sqlResults array.*/
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

		//TABLE 1 - output results

		echo "<h1>Query Results:</h1><br/>";
		echo '<p> Table showing sites meeting the filter criteria. Use the "back" button to revise the filter. </p>';
		/*This is an easy way to structure the output table, have some string combination thing for
		all the passed in variables (from dropdowns) that define the columns as well as for the SQL queries*/
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
		//n.b. this function currently can use the species map to convert things that are
		//values from the options table rather than the animal table
		function arrayToQuery($inputArray,$speciesMap){
			
			$query="SELECT DISTINCT site.site_id FROM site LEFT JOIN photo on site.site_id=photo.site_id";

			$counter=0;
			//counter detects when you are at the start of creating the sql query (for writing select where etc)
			
			$handledGroup1=['species','person_id','contains_human','site_id','habitat_id'];
			//the group of variables to be handled togethor by the main body of the sql creation code below
			$handledGroup1Mapped=['species'];
			//the variables in the group 1 list that have been mapped to something else via options table
			
			$handledGroup2=['time1_form=','time2_form='];
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
						
						if(in_array($key,$handledGroup1Mapped)){
							$rawValue = array_search($value,$speciesMap);
							//raw value is the value in the animal table
							//corresponding to the value in the options table	
						}
						else{
							$rawValue=$value;
						}
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
							}
							
							else{
								$query=$query." AND ".$modifiedKey." = ".$rawValue;
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
								}
								
							else{
									$query=$query." AND ".$modifiedKey." = ";
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
									}
									
									else{
										$query=$query." OR ".$arrayItem;
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
								}
							$counter=$counter+1;
							
							$modifiedStartTime=$_REQUEST['time1_form='];
							$modifiedEndTime=$_REQUEST['time2_form='];
							//start and end time must be modified to take the "T" out of the middle of the string
							//to make it work with the sql format for date and time
							$modifiedStartTime="'".str_ireplace("T"," ",$modifiedStartTime)."'";
							$modifiedEndTime="'".str_ireplace("T"," ",$modifiedEndTime)."'";
							$query=$query." taken BETWEEN ".$modifiedStartTime.' AND '.$modifiedEndTime;
						
						
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
						
						$query=$query." latitude BETWEEN ".$lat1.' AND '.$lat2;
						
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
						
						$query=$query." longitude BETWEEN ".$long1.' AND '.$long2;
						
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
						
						$query=$query."site.site_id IN (SELECT site.site_id FROM site INNER JOIN photo on site.site_id = photo.site_id GROUP BY site.site_id HAVING COUNT(site.site_id) BETWEEN ".$photoCount1.' AND '.$photoCount2.')';
						
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
						
						$query=$query."site.site_id IN (SELECT upload.site_id FROM photosequence INNER JOIN upload on upload.upload_id = photosequence.upload_id GROUP BY upload.site_id HAVING COUNT(upload.site_id) BETWEEN 0 and 200)";
						
						$sequenceCountDone=true;
					}
				}
			}
			echo $query;
			return $query;	
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////

		function searchBetweenDates($connection,$d1,$d2){
			$sql="SELECT photo_id FROM photo WHERE taken BETWEEN '".$d1."' AND '".$d2."'";
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