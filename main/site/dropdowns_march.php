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
		$dbname="mammalweb2";
		
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
		
		$speciesValues=populateCategory($connection,"species","animal");
		
		echo '<form id="inputs" role="form" action="image_display.php" method="post">';
		
		echo'  <label for="speciesSelect">Select species:</label>';
		echo'  <select multiple name="species[]" class="form-control" id="speciesSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($speciesValues as $speciesValue)
		{
			$thisField=strip_tags($speciesMap[$speciesValue]);
			if(!($thisField=="Like")){
			echo'<option value="'.$speciesValue.'">'.$thisField.'</option>';
			}
		}
		echo'  </select>';
		
		$speciesValues=populateCategory($connection,"gender","animal");
		
		echo'  <label for="genderSelect">Select gender:</label>';
		echo'  <select name="gender" class="form-control" id="genderSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($speciesValues as $genderValue)
		{
			$thisField=strip_tags($speciesMap[$genderValue]);
			echo'<option value="'.$genderValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$ageValues=populateCategory($connection,"age","animal");
		
		echo'  <label for="ageSelect">Select age:</label>';
		echo'  <select name="age" class="form-control" id="ageSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($ageValues as $ageValue)
		{
			$thisField=strip_tags($speciesMap[$ageValue]);
			echo'<option value="'.$ageValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$person_idValues=populateCategory($connection,"person_id","photo");
		
		echo'  <label for="person_idSelect">Select person_id:</label>';
		echo'  <select multiple name="person_id[]" class="form-control" id="person_idSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($person_idValues as $person_idValue)
		{
			$thisField=strip_tags($person_idValue);
			echo'<option value="'.$person_idValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$site_idValues=populateCategory($connection,"site_id","photo");
		
		echo'  <label for="site_idSelect">Select Site id:</label>';
		echo'  <select multiple name="site_id[]" class="form-control" id="site_idSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($site_idValues as $site_idValue)
		{
			$thisField=strip_tags($site_idValue);
			echo'<option value="'.$site_idValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$sequence_idValues=populateCategory($connection,"sequence_id","photo");
		
		echo'  <label for="sequence_idSelect">Select sequence:</label>';
		echo'  <select multiple name="sequence_id[]" class="form-control" id="sequence_idSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($sequence_idValues as $sequence_idValue)
		{
			$thisField=strip_tags($sequence_idValue);
			echo'<option value="'.$sequence_idValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
	
		
		echo' <label for="contains_human">Humans Present:</label>';
		echo '<select name ="contains_human" class="form-control" id="contains_humanSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		echo'<option value="1">Yes</option>';
		echo'<option value="0">No</option>';
		echo'</select>';
		
		echo' <label for="flag">Image status:</label>';
		echo '<select name="flag" class="form-control" id="flagSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		echo'<option value=168>Incomplete classification</option>';
		echo'<option value=166>Consensus classification</option>';
		echo'<option value=165>Blank classification</option>';
		echo'<option value=167>Unsure classification- needs attention</option>';
		echo'</select>';
		
		echo'<label for="num_class1">Number of classifications. Between:</label>';
		echo '<input name="num_class1" class="form-control" id="num_class1Input" form="inputs" value="0">';
		echo'<label for="num_class2">and</label>';
		echo '<input name="num_class2" class="form-control" id="num_class2Input" form="inputs" value="0">';
		
		echo' <label for="time1">Search between times:</label>';
		echo'<input type="datetime-local" class="form-control" id="time1Input" name="time1 form="inputs" step="1">';
		echo'</input>';
		
		echo'<input type="datetime-local" class="form-control" id="time2Input" name="time2 form="inputs" step="1">';
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
		<script>
		</script>
</body>
</html>
