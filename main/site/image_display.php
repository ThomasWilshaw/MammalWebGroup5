<html>
<head>
	<title>Php x SQL</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>
<body>

	<a href="dropdowns_march.php">Back</a>  <!--  MAKE THIS POINT BACK TO THE SEARCH pAGE  -->
	<?php 

	//At the moment, three searches are hard coded into the page. Once we know the input format from the search form, change to only output a single table that includes all the fields being searched for.

		$servername="localhost";
		$username="root";
		$password="";
		$dbname="mammalweb2";

		$connection=new mysqli($servername,$username,$password,$dbname);

		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);		
		
		if(isset($_REQUEST)){
			$sql=arrayToQuery($_REQUEST,$speciesMap);
		}
		
		$sqlResults=$connection->query($sql);
	//	var_dump($_REQUEST);
	//	var_dump($sqlResults);

		//TABLE 1 - output results

		echo "<h1>Query Results:</h1><br/>";
		echo '<p> Table showing images meeting the filter criteria: </p>';
		/*This is an easy way to structure the output table, have some string combination thing for
		all the passed in variables (from dropdowns) that define the columns as well as for the SQL queries*/
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
		if($sqlResults->num_rows>0){
			while($row=$sqlResults->fetch_assoc()){
				echo "<tr>";
				echo "<td>".$row["photo_id"]."</td>";
				echo "<td>".$row["site_id"]."</td>";
				echo "<td>".$row["upload_id"]."</td>";
				echo '<td><a href="http://www.mammalweb.org//biodivimages/person_"'.$row["upload_id"].'"/site_'.$row["site_id"].'/'.$row['filename'].'"> View Image </a></td>';
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
		//n.b. this function currently can use the species map to convert things that are
		//values from the options table rather than the animal table
		function arrayToQuery($inputArray,$speciesMap){
						
			$query="SELECT * FROM aggregate INNER JOIN photo ON aggregate.photo_id=photo.photo_id";
			
			$counter=0;
			//counter detects when you are at the start of creating the sql query (for writing select where etc)
			
			$handledGroup1=['species','gender','age','person_id','contains_human','site_id','sequence_id','flag'];
			//the group of variables to be handled togethor by the main body of the sql creation code below
			$handledGroup1Mapped=['species','gender','age'];
			
			$handledGroup2=['time1_form=','time2_form='];
			//the group of variables to be handled in the time section
			
			$handledGroup3=['blank','classified'];
			//the group of variables to be handled in the thid section
			//this section deals with the 'flag' attribute in the table
			
			$handledGroup4=['num_class1','num_class2'];
			//the number of classifications, searches for an attribute between these two variables
			
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
							if($counter==0){
								$query=$query." WHERE ".$key." = ".$rawValue;
							}
							
							else{
								$query=$query." AND ".$key." = ".$rawValue;
							}
							
							$counter=$counter+1;
						}
					}
					
					else{
						if(!in_array("any",$value)){
							//if the "any" option is selected, this overrides other options
							if($counter==0){
									$query=$query." WHERE ".$key." = ";
								}
								
							else{
									$query=$query." AND ".$key." = ";
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
					//relating to the flag attribute
					if(in_array($key,$handledGroup3) AND (!empty($value))){
						
						
						
						
						
						
					}
					
					
					//if the variable is in the fourth behaviour group
					//relating to the num class num_class1<x<num_class2
					if(in_array($key,$handledGroup4) AND (!empty($value))){
						
						$num_classVariablesRecieved+=1;
						
						if($num_classVariablesRecieved==2){//must have 
						//before and after time before the time part of the
						//query can be constructed
						
							if($counter==0){
								$query=$query." WHERE ";
								}
										
							else{
								$query=$query." AND ";
								}
							$counter=$counter+1;
							
							$query=$query." num_class BETWEEN ".$_REQUEST['num_class1'].' AND '.$_REQUEST['num_class2'];
						
						
						}
						
						
						
						
					}
				}
					
			}
			
			$query=$query.";";
			
			//testing
			//echo $query;
			//
			
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
	</table>
</body>
</html>