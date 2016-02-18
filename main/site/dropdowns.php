<!DOCTYPE html>
 <!--  PHP DIRECTLY ADAPTED FROM WILL'S PHP FILE  -->
  <!-- FOR POPULATING DROPDOWN MENUS WITH THINGS FROM DATABASE -->
<html>

<head>
	<title>PHP DROPDOWN MENU POPULATION FROM TABLES</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css">
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

	
		$dropdownCategories=getCategories($connection);//An array. uses the function below to get an array of the categories in the table
	
		//visible part of page:
		echo "<h2>Dropdown:</h2><br/>";
		
		
		//the dropdowns themselves:
		//NOTE, the dropdown for each attribute here is hardcoded
		//but this is not necessary.
		//since the function getCategories can return an array of attributes in a table
		//it would be easy enough to then generate a dropdown for each attribute, outputting similar
		//stuff to the repeated echoing below
		
		$speciesValues=populateCategory($connection,"species");
		
		echo'<div class="form-group">';
		echo'  <label for="speciesSelect">Select species:</label>';
		echo'  <select class="form-control" id="speciesSelect">';
		foreach($speciesValues as $speciesValue)
			echo'<option>'.$speciesValue.'</option>';
		echo'  </select>';
		echo'</div>';
		
		$speciesValues=populateCategory($connection,"gender");
		
		echo'<div class="form-group">';
		echo'  <label for="genderSelect">Select gender:</label>';
		echo'  <select class="form-control" id="genderSelect">';
		foreach($speciesValues as $genderValue)
			echo'<option>'.$genderValue.'</option>';
		echo'  </select>';
		echo'</div>';
		
		$ageValues=populateCategory($connection,"age");
		
		echo'<div class="form-group">';
		echo'  <label for="ageSelect">Select age:</label>';
		echo'  <select class="form-control" id="ageSelect">';
		foreach($ageValues as $ageValue)
			echo'<option>'.$ageValue.'</option>';
		echo'  </select>';
		echo'</div>';
		
		
		
		
		// Two custom functions: 
		//getCategories: returns all the attributes in a table as an array
		//populateCategory: returns an array of all unique values for a given category in the database, as an array
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
		?>
</body>
</html>
