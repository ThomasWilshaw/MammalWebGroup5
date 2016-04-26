<!DOCTYPE html>
 <!--  PHP with functions to populate dropdowns and generate sql queries  -->
<html>

<head>
	<title>PHP DROPDOWN MENU POPULATION FROM TABLES</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

</head>

<body>

	<?php 
		
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection

		$dropdownCategories=getCategories($connection);
		//An array. uses the function below to get an array of the categories in the table
		
		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);
		
		//visible part of page:
		echo "<h1>Search Images:</h1><br/>";
		
		/*the dropdowns themselves:
		NOTE, the dropdown for each attribute here is hardcoded
		but this is not necessary.
		since the function getCategories can return an array of attributes in a table
		it would be easy enough to then generate a dropdown for each attribute, outputting similar
		stuff to the repeated echoing below */
		
		echo '<form id="inputs" role="form" action="image_display.php" method="post">';
		echo '<div class="container">';
			
			$speciesValues=populateCategory($connection,"species","animal");
			
			echo'<div class="row">';
			
				echo'<div class="col-sm-4">';
					echo'  <label for="speciesSelect">Specific species:</label>';
					echo'  <select multiple name="species[]" class="form-control" id="speciesSelect" form="inputs" size=5>';
					echo'<option value="any">Any</option>';
					foreach($speciesValues as $speciesValue)
					{
						$thisField=strip_tags($speciesMap[$speciesValue]);
						if(!($thisField=="Like")){
						echo'<option value="'.$speciesValue.'">'.$thisField.'</option>';
						}
					}
					echo'  </select>';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					$speciesValues=populateCategory($connection,"gender","animal");
					echo'<div class="form-group">';
						echo'  <label for="genderSelect">Specific gender:</label>';
						echo'  <select name="gender" class="form-control" id="genderSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($speciesValues as $genderValue)
						{
							$thisField=strip_tags($speciesMap[$genderValue]);
							echo'<option value="'.$genderValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					$ageValues=populateCategory($connection,"age","animal");
					echo'<div class="form-group">';
						echo'  <label for="ageSelect">Specific age category:</label>';
						echo'  <select name="age" class="form-control" id="ageSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($ageValues as $ageValue)
						{
							$thisField=strip_tags($speciesMap[$ageValue]);
							echo'<option value="'.$ageValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
			echo'</div>';
			
			echo'<div class="row">';
				echo'<br/>';
			echo'</div>';
				
			echo'<div class="row">';
			
				echo'<div class="col-sm-4">';
					$person_idValues=populateCategory($connection,"person_id","photo");
					echo'<div class="form-group">';
						echo'  <label for="person_idSelect">Specific person_id:</label>';
						echo'  <select multiple name="person_id[]" class="form-control" id="person_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($person_idValues as $person_idValue)
						{
							$thisField=strip_tags($person_idValue);
							echo'<option value="'.$person_idValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					$site_idValues=populateCategory($connection,"site_id","photo");
					echo'<div class="form-group">';
						echo'  <label for="site_idSelect">Specific Site id:</label>';
						echo'  <select multiple name="site_id[]" class="form-control" id="site_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($site_idValues as $site_idValue)
						{
							$thisField=strip_tags($site_idValue);
							echo'<option value="'.$site_idValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					$sequence_idValues=populateCategory($connection,"sequence_id","photo");
					echo'<div class="form-group">';
						echo'  <label for="sequence_idSelect">Specific sequence:</label>';
						echo'  <select multiple name="sequence_id[]" class="form-control" id="sequence_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($sequence_idValues as $sequence_idValue)
						{
							$thisField=strip_tags($sequence_idValue);
							echo'<option value="'.$sequence_idValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
					echo'</div>';
				echo'</div>';
			echo'</div>';
			
			echo'<div class="row">';
			
				echo'<br/>';
				
				echo'<div class="col-sm-4">';
					echo'<div class="form-group">';
						echo' <label for="contains_human">Humans Present:</label><br/>';
						echo'<input type="radio" name="contains_human" value=1 form="inputs">Yes<br/>';
						echo'<input type="radio" name="contains_human" value=0 form="inputs">No<br/>';
						echo'<input type="radio" name="contains_human" value="any" form="inputs">Any<br/>';
					echo'</div>';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					echo'<div class="form-group">';
						echo' <label for="flag[]" >Specific status:</label><br/>';
						echo'<input type="checkbox" name="flag[]" value=168 form="inputs">Incomplete classification<br/>';
						echo'<input type="checkbox" name="flag[]" value=166 form="inputs">Classified with certainty<br/>';
						echo'<input type="checkbox" name="flag[]" value=165 form="inputs">Classified as blank<br/>';
						echo'<input type="checkbox" name="flag[]" value=167 form="inputs">Unsure classification- needs attention<br/>';
						echo'<br/>';
					echo'</div>';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					$habitat_idValues=populateCategory($connection,"habitat_id","site");
					echo'<div class="form-group">';
						echo'  <label for="site_idSelect">Specific habitats:</label>';
						echo'  <select multiple name="habitat_id[]" class="form-control" id="habitat_idSelect" form="inputs" size=5>';
						echo'<option value="any">Any</option>';
						foreach($habitat_idValues as $habitat_idValue)
						{
							$thisField=strip_tags($speciesMap[$habitat_idValue]);
							echo'<option value="'.$habitat_idValue.'">'.$thisField.'</option>';
						}
						echo'  </select>';
						echo'</div>';		
					echo'</div>';
			echo'</div>';
			
			echo'<div class="row">';
				echo'<div class="col-sm-6">';
					echo'<div class="form-group">';
						echo'<label for="num_class1">Number of classifications. Between:</label>';
						echo '<input type="number" name="num_class1" class="form-control" id="num_class1Input" form="inputs" value="0">';
						echo'<label for="num_class2">and</label>';
						echo '<input type="number" name="num_class2" class="form-control" id="num_class2Input" form="inputs" value="0">';
					echo'</div>';
				echo'</div>';
				
				echo'<div class="col-sm-6">';
					echo'<div class="form-group">';
						echo' <label for="time1">Between specific times:</label>';
						echo'<input type="datetime-local" class="form-control" id="time1Input" name="time1" form="inputs" step="1">';
						echo'<input type="datetime-local" class="form-control" id="time2Input" name="time2" form="inputs" step="1">';
					echo'</div>';
				echo'</div>';
			echo'</div>';
			
			echo'<div class="row">';
				echo'<div class="col-sm-4">';
					echo'<label id="latLabelLabel">Latitude:</label>';
					echo'<br/>';
					echo'<label for="lat1" id="latLabel1">Between</label>';
					echo'<input type = "number" step="any" name="lat1" class="form-control" id="lat1" form="inputs">';
					echo'<label for="lat2" id="latLabel2">and</label>';
					echo'<input type = "number" step="any" name="lat2" class="form-control" id="lat2" form="inputs">';
				echo'</div>';
				
				echo'<div class="col-sm-4">';
					echo'<label id="longLabelLabel">Longitude:</label>';
					echo'<br/>';
					echo'<label for="long1" id="longLabel1">Between</label>';
					echo'<input type = "number" step="any" name="long1" class="form-control" id="long1" form="inputs">';
					echo'<label for="long2" id="longLabel2">and</label>';
					echo'<input type = "number" step="any" name="long2" class="form-control" id="long2" form="inputs">';
				echo'</div>';
				
				echo'<div class="col-sm-1">';
					echo'<br/><br/>';
					echo'<button type="button" class="btn btn-secondary" id="mapButton" onClick="drawMap()">Toggle Map</button>';
				echo'</div>';
				echo'<div class="col-sm-3">';
					echo'<p id="mapInfo" style="visibility:hidden;">-Left click on the map to place a corner of your selection area.';
					echo'<br/>';
					echo'-Click again to place the opposite corner and draw the box.';
					echo'<br/>';
					echo'-A third click clears the selection box';
					echo'</p>';
				echo'</div>';
			echo'</div>';
			
			echo'<br/>';
			
			echo'<div class="row">';
				echo'<div class="col-sm-16" id="mapDiv" style="height:0px;visibility:hidden">';
					
				echo'</div>';
			echo'</div>';
			
			echo'<br/><br/>';
			
			echo '<input type="submit" class="btn btn-primary btn-lg btn-block" value="Submit"> ';	
			
		echo '</div>';
		echo '</form>';
		
		$connection->close();//closes connection when you're done with it
		
		/* Four custom functions: 
		
		getCategories: returns all the attributes in a table as an array
		
		populateCategory: returns an array of all unique values for a given category in the database, as an array
		
		loadSpeciesMap: from will's code - using the options table to convert integer values stored in tables to 
		the relevant string
				e.g. 2 might represent a species of "bear" 
				
		*/
		/////////////////////////////////////////////////////////////////////////////////////////////
		function getCategories($connection){//returns attributes of a table as an array
			//creating and sending query
			$sql="SHOW COLUMNS FROM `animal`";  //replace "animal" with any other table part of the database initialised in the dbname variable.
			$categoryQuery=$connection->query($sql);
			//using query results
			$categoryArray=array();
			while($attribute=$categoryQuery->fetch_assoc()){
				array_push($categoryArray,$attribute['Field']);
			}
			return $categoryArray;
		}
		////////////////////////////////////////////////////////////////////////////////////////////////////	
		function populateCategory($connection,$category,$tableName){
			//returns an array of possible values for an attribute that appear in the database
			//in the named table
			//creating and sending query
			$sql="SELECT DISTINCT ".$category." FROM `".$tableName."`"; 
			//replace "animal" with any other table part of the database initialised in the dbname variable.
			$categoryQuery=$connection->query($sql);
			//using query results
			$categoryArray=array();
			while($attribute=$categoryQuery->fetch_assoc()){
				if(trim($attribute[$category])!=""){
					array_push($categoryArray,$attribute[$category]);
				}
			}
			return $categoryArray;			
		}
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
		?>
		
	<script>//map related functions
		var map;
		var markerList=[];//an array with my markers (up to 2);
		
		function hideMap(){
			//hide and shrink the div that shows the google map
			$('#mapDiv').attr('style',"height:0px;visibility:hidden");
			//clicking the toggle button again will show the map
			$('#mapButton').attr('onClick',"drawMap()");
			//hides map usage info
			$('#mapInfo').attr('style',"visibility:hidden");
		}
		
		//used to clear an array of markers from my google map
		function clearArrayOfMarkers(markerArray){
			for (item in markerArray) {
				markerArray[item].setMap(null);
			}
		}
		
		//these will hold the coordinates of the box when selected
		var latitude1=0;
		var longitude1=0;
		var latitude2=0;
		var longitude2=0;
		
		//this variable is my polyline
		var myPolyLine;
		
		function drawMap() {
			//expand and show the div that will display the google map
			$('#mapDiv').attr('style',"height:800px;visibility:visible");
			//display map usage info
			$('#mapInfo').attr('style',"visibility:visible");
			//create the google map
			map = new google.maps.Map(document.getElementById('mapDiv'), {
			center: {lat: 54.7650, lng: -1.5782},
			zoom: 8
			});
	
			var pointsClicked=0;
			//listener for click event
            google.maps.event.addListener(map, "click", function(event) {
				//user clicks at a specific latitude and longitude
                var latitude = event.latLng.lat();
                var longitude = event.latLng.lng();
				
				if(pointsClicked==0)//if this is the first point, the top left of the box
				{
				//places marker there
                  var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(latitude,longitude),
                        map: map,
                  });
				  markerList.push(marker);
				  latitude1=latitude;
				  longitude1=longitude;
				  //update top left lat long values, all to 6 d.p.
				  document.getElementById("lat1").value = latitude.toFixed(6);
				  document.getElementById("long1").value = longitude.toFixed(6);
				  pointsClicked+=1;//count this point as clicked
				}
				   
				else
				{
					if(pointsClicked==1)//if this is the second, the bottom right
					{
					//places marker there
					  var marker = new google.maps.Marker({
							position: new google.maps.LatLng(latitude,longitude),
							map: map,
					  });
					  markerList.push(marker);
					  latitude2=latitude;
					  longitude2=longitude;
					  //update bottom right lat long values, all to 6 d.p.
					  document.getElementById("lat2").value = latitude.toFixed(6);
					  document.getElementById("long2").value = longitude.toFixed(6);
					  pointsClicked+=1;//count this point as clicked
					  
					  //draw the box
					  assignBox();
					}   
					else{
						pointsClicked=0;//resets points clicked if something went wrong or over clicked
						clearArrayOfMarkers(markerList);//clearing markers from map
						markerList=[];
						removeBox();
					}
				}	
            });
			
			//draw my box between selected points on map
			function assignBox(){
				var corners=[
					{lat: latitude1, lng: longitude1},
					{lat: latitude1, lng: longitude2},
					{lat: latitude2, lng: longitude2},
					{lat: latitude2, lng: longitude1},
					{lat: latitude1, lng: longitude1}
				  ];
				myPolyLine = new google.maps.Polyline({
					path: corners,
					strokeColor: '#FF0000',
					strokeOpacity: 1.0,
					strokeWeight: 1
				  });
				myPolyLine.setMap(map);
			}
			
			//undraw my box between selected points on map
			function removeBox(){
				myPolyLine.setMap(null);
			}
			
		
			//clicking the toggle button again will hide the map
			$('#mapButton').attr('onClick',"hideMap()");
			}
	</script>
	 <!--google maps javascript api-->
	 <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3bN3ZwaXsZ2Eloq_4KOn2CQrXcvL6fIo" async defer></script>
</body>
</html>
