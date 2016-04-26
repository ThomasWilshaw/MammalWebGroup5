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
		
		//escapes all $_REQUEST data against potential sql injection
		makeSecureForSQL($connection);
		
		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);	

		$description="none";//will contain an english description of filter criteria specified		
		$counter=0;
		
		if(isset($_REQUEST)){
			$sqlArray=arrayToQuery($_REQUEST,$speciesMap);
			$sql=$sqlArray[0];
			$description=$sqlArray[1];
		}
		
		$sqlResults=$connection->query($sql);
		$counter=mysqli_num_rows($sqlResults);

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
				echo'<a href="dropdowns_images.php" class="btn btn-info btn-lg btn-block" role="button">Back to filter selection</a>';
				echo'<br/>';
				echo'<a href="exportCSV.php?data='.$sql.'" class="btn btn-primary btn-lg btn-block" role="button">Download results</a>';
			echo '</div>';
		echo'</div>';
		/*output table showing sites meeting the filter criteria */
		echo '<table class="table table-hover">';
		echo '<thead>';
		echo "<tr>";
		echo '<th>Photo ID</th>';
		echo '<th>Photo Site</th>';
		echo '<th>ID of uploader</th>';
		echo '<th>Preview</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		if(isset($sqlResults->num_rows) && $sqlResults->num_rows>0){ 
			while($row=$sqlResults->fetch_assoc()){
                $imagePath = "http://www.mammalweb.org//biodivimages/person_" . $row["person_id"] . "/site_" . $row["site_id"] . "/" . $row['filename'];
				echo "<tr>";
				echo "<td>".$row["photo_id"]."</td>";
				echo "<td>".$row["site_id"]."</td>";
				echo "<td>".$row["person_id"]."</td>";
                echo "<td><a href=\"javascript:;\" src=$imagePath" . " onclick=\"popUp(this)\"" . "> View Image </a></td>";
				echo "</tr>";
			}
		}
		else{
			echo "<tr><td>No images found</td></tr>";
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
		/*generates an sql query using the variables in $_REQUEST.
		Returns an array where index 0 contains the query, 
		index 1 contains the description */
		function arrayToQuery($inputArray,$speciesMap){
			
			$query="SELECT * FROM aggregate INNER JOIN photo ON aggregate.photo_id=photo.photo_id";
			$description="";//the list of filter criteria
			
			if((isset($_REQUEST['habitat_id']) and ($_REQUEST['habitat_id'][0]!="any")) or (isset($_REQUEST['lat2'])) or (isset($_REQUEST['long1'])) or (isset($_REQUEST['long2'])) or (isset($_REQUEST['lat1'])) ){
					$query="SELECT * FROM aggregate INNER JOIN photo ON aggregate.photo_id=photo.photo_id INNER JOIN site ON photo.site_id=site.site_id";
			}
			//if a habitat filter is also set, the base SQL query needs to be extended, above
			//could always do this for all cases, but best not to as it creates a larger table to query.
			
			
			$counter=0;
			//counter detects when you are at the start of creating the sql query (for writing select where etc)
			
			$handledGroup1=['species','gender','age','person_id','contains_human','site_id','sequence_id','flag','habitat_id'];
			//the group of variables to be handled togethor by the main body of the sql creation code below
			$handledGroup1Mapped=['species','gender','age'];
			
			$handledGroup2=['time1','time2'];
			//the group of variables to be handled in the time section
			
			$handledGroup3=['blank','classified'];
			//the group of variables to be handled in the thid section
			//this section deals with the 'flag' attribute in the table
			
			$handledGroup4=['num_class1','num_class2'];
			//the number of classifications, searches for an attribute between these two variables
			
			$handledGroup5=['lat1','lat2'];
			//latitude and longitude boundaries
			$latDone=false;
			
			$handledGroup6=['long1','long2'];
			//latitude and longitude boundaries
			$longDone=false;
			
			$timeVariablesRecieved=0;
			//used to count the number of time variables recieved,
			//since two must be recieved before the time part of the query can be constructed
			//(before and after)
			
			$num_classVariablesRecieved=0;
			//used to count the number of classification variables recieved
			
			foreach($inputArray as $key => $value){
				
				if(in_array($key,$handledGroup1)){//if this is a variable on the list to be handled here
					
					if(!(is_array($value))){
						
						if(in_array($key,$handledGroup1Mapped)){
							$rawValue = array_search($value,$speciesMap);
							//raw value is the value in the animal table
							//corresponding to the value in the options table	
							$descriptionValue=$speciesMap[$value];
						}
						else{
							$rawValue=$value;
							$descriptionValue=$value;
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
							if($counter==0){
								$query=$query." WHERE ".$key." = ".$rawValue;
								$description=$description.$key." = ".$descriptionValue;
							}
							
							else{
								$query=$query." AND ".$key." = ".$rawValue;
								$description=$description.",".$key." = ".$descriptionValue;
							}
							
							$counter=$counter+1;
						}
					}
					
					else{
						if(!in_array("any",$value)){
							//if the "any" option is selected, this overrides other options
							if($counter==0){
								$query=$query." WHERE (".$key." = ";
								$description=$description.$key." = ";
							}
							
							else{
								$query=$query." AND (".$key." = ";
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
									
									if(in_array($key,$handledGroup1Mapped)){//if it is mapped in options table
										$descriptionValue=$speciesMap[$arrayItem];
									}
									else{
										$descriptionValue=$arrayItem;
									}
									
									if($innerCounter==0){
										$query=$query.$arrayItem;
										$description=$description.$descriptionValue;
									}
									
									else{
										$query=$query." OR ".$arrayItem;
										$description=$description." or ".$descriptionValue;
									}
									$innerCounter+=1;
								}	
									
							}
							$query=$query.")";
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
								$query=$query." WHERE ".$modifiedKey." = ".$rawValue;
								$description=$description.$key." = ".$rawValue;
							}
							
							else{
								$query=$query." AND ".$modifiedKey." = ".$rawValue;
								$description=$description.",".$key." = ".$rawValue;
							}
							$counter=$counter+1;
							
							$modifiedStartTime=$_REQUEST['time1'];
							$modifiedEndTime=$_REQUEST['time2'];
							//start and end time must be modified to take the "T" out of the middle of the string
							//to make it work with the sql format for date and time
							$modifiedStartTime="'".str_ireplace("T"," ",$modifiedStartTime)."'";
							$modifiedEndTime="'".str_ireplace("T"," ",$modifiedEndTime)."'";
							$query=$query." taken BETWEEN ".$modifiedStartTime.' AND '.$modifiedEndTime;
							$description=$description." taken between ".$modifiedStartTime." and ".$modifiedEndTime;
						
						
						}
					}
					
					//if the variable is in the fourth behaviour group
					//relating to the num class num_class1<x<num_class2
					else if(in_array($key,$handledGroup4) AND (!empty($value))){
						
						$num_classVariablesRecieved+=1;
						
						if($num_classVariablesRecieved==2){//must have 
						//before and after time before the time part of the
						//query can be constructed
						
							if($counter==0){
								$query=$query." WHERE ".$modifiedKey." = ".$rawValue;
								$description=$description.$key." = ".$rawValue;
							}
							
							else{
								$query=$query." AND ".$modifiedKey." = ".$rawValue;
								$description=$description.",".$key." = ".$rawValue;
							}
							$counter=$counter+1;
							$numClass1=$_REQUEST['num_class1'];
							$numClass2=$_REQUEST['num_class2'];
							if($numClass1<=$numClass2){
								$numClassLower=$numClass1;
								$numClassHigher=$numClass2;
							}
							else{
								$numClassHigher=$numClass1;
								$numClassLower=$numClass2;
						}
							$query=$query." num_class BETWEEN ".$numClassLower.' AND '.$numClassHigher;
							$description=$description."with between ".$numClassLower." and ".$numClassHigher." classifications";		
						}	
					}
					//if the variable is in the third behaviour group
					//relating to latitude boundaries
					else if(in_array($key,$handledGroup5) AND (!empty($value)) AND (!$latDone)){
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
					 else if(in_array($key,$handledGroup6) AND (!empty($value)) AND (!$longDone)){
						
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
				}
					
			}
			$query=$query.";";
			$results=array();
			$results[0]=$query;
			$results[1]=$description;
			return $results;
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
	</table>
    
    <!-- Creates the bootstrap modal where the image will appear -->
    <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel">Image preview</h4>
          </div>
          <div class="modal-body">
            <img src="" id="imagepreview" style="width: 400px; height: 264px;" >
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" onclick="fullScreen()">Full Screen</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    
    <script>
        function popUp(param){
            $('#imagepreview').attr('src', param.getAttribute('src')); // here asign the image to the modal when the user click the enlarge link
            $('#imagemodal').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function
        }
        
        function fullScreen(){
            window.location = $('#imagepreview').attr('src');
        }
    </script>
</body>
</html>
