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
		$password="toot";
		$dbname="mammalwebdump";
		
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection

		$dropdownCategories=getCategories($connection);//An array. uses the function below to get an array of the categories in the table
		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);
		
		//visible part of page:
		echo "<h2>Dropdown:</h2><br/>";
		
		//the dropdowns themselves:
		//NOTE, the dropdown for each attribute here is hardcoded
		//but this is not necessary.
		//since the function getCategories can return an array of attributes in a table
		//it would be easy enough to then generate a dropdown for each attribute, outputting similar
		//stuff to the repeated echoing below
		
		$speciesValues=populateCategory($connection,"species");
		
		echo '<form id="inputs" role="form">';
		echo'<div class="form-group">';
		echo'  <label for="speciesSelect">Select species:</label>';
		echo'  <select class="form-control" id="speciesSelect">';
		foreach($speciesValues as $speciesValue)
			echo'<option>'.$speciesMap[$speciesValue].'</option>';
		echo'  </select>';
		echo'</div>';
		
		$speciesValues=populateCategory($connection,"gender");
		
		echo'<div class="form-group">';
		echo'  <label for="genderSelect">Select gender:</label>';
		echo'  <select class="form-control" id="genderSelect">';
		foreach($speciesValues as $genderValue)
			echo'<option>'.$speciesMap[$genderValue].'</option>';
		echo'  </select>';
		echo'</div>';
		
		$ageValues=populateCategory($connection,"age");
		
		echo'<div class="form-group">';
		echo'  <label for="ageSelect">Select age:</label>';
		echo'  <select class="form-control" id="ageSelect">';
		foreach($ageValues as $ageValue)
			echo'<option>'.$speciesMap[$ageValue].'</option>';
		echo'  </select>';
		echo'</div>';
		
		$person_idValues=populateCategory($connection,"person_id");
		
		echo'<div class="form-group">';
		echo'  <label for="person_idSelect">Select person_id:</label>';
		echo'  <select class="form-control" id="person_idSelect">';
		foreach($person_idValues as $person_idValue)
			echo'<option>'.$person_idValue.'</option>';
		echo'  </select>';
		echo'</div>';

		echo '<button type="submit" class="btn btn-default" id="sendFormButton">Search</button>';
		echo '</form>';
		
		$connection->close();//closes connection when you're done with it
		
		// Two custom functions: 
		//getCategories: returns all the attributes in a table as an array
		//populateCategory: returns an array of all unique values for a given category in the database, as an array
		//loadSpeciesMap: from will's code - using the options table to convert integer values stored in tables to the relevant string
		//		e.g. 2 might represent a species of "bear"
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
			$sql="SELECT DISTINCT ".$category." FROM `animal`";  //replace "animal" with any other table part of the database initialised in the dbname variable.
			$categoryQuery=$connection->query($sql);
			//using query results
			$categoryArray=array();
			while($attribute=$categoryQuery->fetch_assoc()){
				array_push($categoryArray,$attribute[$category]);
			}
			return $categoryArray;			
		}
		///////////////////////////////////////////////////////////////////////////////////////////////////////
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
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		?>
		<script>
			$("#sendFormButton").click(function(){
				console.log($("#inputs").serialize());
			})
		</script>
</body>
</html>
