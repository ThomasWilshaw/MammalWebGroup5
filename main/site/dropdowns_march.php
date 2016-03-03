<!DOCTYPE html>
 <!--  PHP DIRECTLY ADAPTED FROM WILL'S PHP FILE  -->
  <!-- FOR POPULATING DROPDOWN MENUS WITH THINGS FROM DATABASE -->
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
		echo "<h2>Dropdown:</h2><br/>";
		
		/*the dropdowns themselves:
		NOTE, the dropdown for each attribute here is hardcoded
		but this is not necessary.
		since the function getCategories can return an array of attributes in a table
		it would be easy enough to then generate a dropdown for each attribute, outputting similar
		stuff to the repeated echoing below */
		
		$speciesValues=populateCategory($connection,"species");
		
		echo '<form id="inputs" role="form" action="dropdowns_march.php" method="post">';
		
		echo'  <label for="speciesSelect">Select species:</label>';
		echo'  <select name="species" class="form-control" id="speciesSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($speciesValues as $speciesValue)
		{
			$thisField=strip_tags($speciesMap[$speciesValue]);
			echo'<option value="'.$thisField.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$speciesValues=populateCategory($connection,"gender");
		
		echo'  <label for="genderSelect">Select gender:</label>';
		echo'  <select name="gender" class="form-control" id="genderSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($speciesValues as $genderValue)
		{
			$thisField=strip_tags($speciesMap[$genderValue]);
			echo'<option value="'.$thisField.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$ageValues=populateCategory($connection,"age");
		
		echo'  <label for="ageSelect">Select age:</label>';
		echo'  <select name="age" class="form-control" id="ageSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($ageValues as $ageValue)
		{
			$thisField=strip_tags($speciesMap[$ageValue]);
			echo'<option value="'.$thisField.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		
		$person_idValues=populateCategory($connection,"person_id");
		
		echo'  <label for="person_idSelect">Select person_id:</label>';
		echo'  <select name="person_id" class="form-control" id="person_idSelect" form="inputs">';
		echo'<option value="any">Any</option>';
		foreach($person_idValues as $person_idValue)
		{
			$thisField=strip_tags($person_idValue);
			echo'<option value="'.$thisField.'">'.$thisField.'</option>';
		}
		echo'  </select>';

		echo '<input type="submit" class="btn btn-default" value="Submit"></button> ';
		echo '</form>';
		
		if(isset($_REQUEST)){
			arrayToQuery($_REQUEST,$speciesMap);
		}
		
		$connection->close();//closes connection when you're done with it
		
		/* Four custom functions: 
		
		getCategories: returns all the attributes in a table as an array
		
		populateCategory: returns an array of all unique values for a given category in the database, as an array
		
		loadSpeciesMap: from will's code - using the options table to convert integer values stored in tables to 
		the relevant string
				e.g. 2 might represent a species of "bear" 
				
		arrayToQuery: takes in an associative array storing what to search for each attribute, and returns a
		string containing an SQL query. 
				e.g. for input: ['species'->'bear','gender'->'male' ]
				returns the string:
				SELECT * FROM 'animal' WHERE species = bear AND gender = male
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
		//n.b. this function currently assumes the values in the array are as in the options table
		//e.g. "Badger" rather than "10"
		//and converts to the relevant value in the animals table
		//so badger becomes 10
		//to change this, delete the rawValue variable and change all occurences of rawValue to value
		function arrayToQuery($inputArray,$speciesMap){
			$query="SELECT * FROM 'animal' WHERE";
			$counter=0;
			
			foreach($inputArray as $key => $value){

				$rawValue = array_search($value,$speciesMap);
				//raw value is the value in the animal table
				//corresponding to the value in the options table
				
				if($counter==0){
				$query=$query." ".$key." = ".$rawValue;
				}
				
				else{
				$query=$query." AND ".$key." = ".$rawValue;
				}
				
				$counter=$counter+1;
				
			}
			$query=$query.";";
			
			//testing
			echo $query;
			//
			
			return $query;	
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		?>
		<script>
		</script>
</body>
</html>
