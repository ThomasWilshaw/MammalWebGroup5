<!DOCTYPE html>
 <!--  PHP with functions to populate dropdowns and generate sql queries  -->
<html>

<head>
	<title>PHP DROPDOWN MENU POPULATION FROM TABLES</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>

<body>

	<?php 
		//sql details
		$servername="localhost";
		$username="root";
		$password="";
		$dbname="mammalweb1";
		
		//establish connection
		$connection=new mysqli($servername,$username,$password,$dbname);//establishes the sql connection

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
		
		$speciesValues=populateCategory($connection,"species");
		
		echo '<form id="inputs" role="form" action="image_display.php" method="post">';
		
		echo'  <label for="speciesSelect">Select species:</label>';
		echo'  <select multiple name="species[]" class="form-control" id="speciesSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($speciesValues as $speciesValue)
		{
			$thisField=strip_tags($speciesMap[$speciesValue]);
			echo'<option value="'.$speciesValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$speciesValues=populateCategory($connection,"gender");
		
		echo'  <label for="genderSelect">Select gender:</label>';
		echo'  <select name="gender" class="form-control" id="genderSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($speciesValues as $genderValue)
		{
			$thisField=strip_tags($speciesMap[$genderValue]);
			echo'<option value="'.$genderValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$ageValues=populateCategory($connection,"age");
		
		echo'  <label for="ageSelect">Select age:</label>';
		echo'  <select name="age" class="form-control" id="ageSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($ageValues as $ageValue)
		{
			$thisField=strip_tags($speciesMap[$ageValue]);
			echo'<option value="'.$ageValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$person_idValues=populateCategoryPhoto($connection,"person_id");
		
		echo'  <label for="person_idSelect">Select person_id:</label>';
		echo'  <select multiple name="person_id[]" class="form-control" id="person_idSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($person_idValues as $person_idValue)
		{
			$thisField=strip_tags($person_idValue);
			echo'<option value="'.$person_idValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		echo' <label for="contains_human">Humans Present:</label>';
		echo '<select name ="contains_human" class="form-control" id="contains_humanSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		echo'<option value="1">Yes</option>';
		echo'<option value="0">No</option>';
		echo'</select>';
		
		echo'Search between times:';
		echo'<input type="datetime-local" class="form-control" id="time1Input" name="time1 form="inputs">';
		echo'</input>';
		
		echo'<input type="datetime-local" class="form-control" id="time2Input" name="time2 form="inputs">';
		echo'</input>';
		
		echo '<input type="submit" class="btn btn-default" value="Submit"></button> ';		
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
		function populateCategory($connection,$category){//returns an array of possible values for an attribute that appear in the database
			//creating and sending query
			$sql="SELECT DISTINCT ".$category." FROM `animal`"; 
			//replace "animal" with any other table part of the database initialised in the dbname variable.
			$categoryQuery=$connection->query($sql);
			//using query results
			$categoryArray=array();
			while($attribute=$categoryQuery->fetch_assoc()){
				array_push($categoryArray,$attribute[$category]);
			}
			return $categoryArray;			
		}
		///////////////////////////////////////////////////////////////////////////////////////////////////
			function populateCategoryPhoto($connection,$category){//returns an array of possible values for an attribute that appear in the photo table
			//creating and sending query
			$sql="SELECT DISTINCT ".$category." FROM `photo`"; 
			//replace "animal" with any other table part of the database initialised in the dbname variable.
			$categoryQuery=$connection->query($sql);
			//using query results
			$categoryArray=array();
			while($attribute=$categoryQuery->fetch_assoc()){
				array_push($categoryArray,$attribute[$category]);
			}
			return $categoryArray;			
		}
		/////////////////////////////////////////////////////////////////////////////////////////////
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
		<script>
		</script>
</body>
</html>
