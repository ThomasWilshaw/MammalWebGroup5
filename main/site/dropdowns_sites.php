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
		echo "<h1>Search Sites:</h1><br/>";
		
		/*the dropdowns themselves:
		NOTE, the dropdown for each attribute here is hardcoded
		but this is not necessary.
		since the function getCategories can return an array of attributes in a table
		it would be easy enough to then generate a dropdown for each attribute, outputting similar
		stuff to the repeated echoing below */
		
		
		echo '<div class="container">';
		
		$speciesValues=populateCategory($connection,"species","animal");
		
		echo'<div class="row">';
		
		echo'<div class="col-sm-4">';
		
		echo '<form id="inputs" role="form" action="site_display.php" method="post">';
		
		echo'  <label for="speciesSelect">Specific species spotted at site:</label>';
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
		
				echo'<div class="form-group">';
		echo' <label for="contains_human">Humans Present:</label><br/>';
		echo'<input type="radio" name="contains_human" value=1 form="inputs">Yes</input><br/>';
		echo'<input type="radio" name="contains_human" value=0 form="inputs">No</input><br/>';
		echo'<input type="radio" name="contains_human" value="any" form="inputs">Any</input><br/>';
		echo'</div>';
		
		echo'</div>';
		echo'</div>';
		
		echo'<div class="row">';
		echo'<br/>';
		echo'</div>';
		
		echo'<div class="row">';
		echo'<div class="col-sm-4">';
		
		echo'NOT USED';
		
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
		
		echo'NOT USED';
		
		echo'</div>';
		echo'</div>';
		
		echo'<div class="row">';
		echo'<br/>';
		echo'<div class="col-sm-4">';
		echo'NOT USED';
		echo'</div>';
		
		echo'<div class="col-sm-4">';
		
		echo'NOT USED';
		
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
		
		
		
		echo'<div class="form-group">';
		echo' <label for="time1">Between specific times:</label>';
		echo'<input type="datetime-local" class="form-control" id="time1Input" name="time1 form="inputs" step="1">';
		echo'</input>';
		echo'<input type="datetime-local" class="form-control" id="time2Input" name="time2 form="inputs" step="1">';
		echo'</input>';
		echo'</div>';
		
		echo '<input type="submit" class="btn btn-default" value="Submit"></button> ';		
		echo '</form>';
		
		echo '</div>';
		
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
