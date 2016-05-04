<!DOCTYPE html>
 <!--  PHP with functions to populate dropdowns and generate sql queries  
 
 Allow access to simple statistics and download of the Mammal Web database (http://www.mammalweb.org/)
Copyright (C) 2016  Freddie Keen, Quentin Lam, Will Taylor, Tom White, 
Thomas Wilshaw
contact: cs-seg5@durham.ac.uk


This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.-->
<html>

<head>
	<title>select user</title>
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
	<script	src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

</head>

<body>
	<?php
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection
		
		//visible part of page:
		echo "<h1>Select User ID to see their correct classifications:</h1><br/>";
		
		/*the dropdowns themselves:
		NOTE, the dropdown for each attribute here is hardcoded
		but this is not necessary.
		since the function getCategories can return an array of attributes in a table
		it would be easy enough to then generate a dropdown for each attribute, outputting similar
		stuff to the repeated echoing below */
		
		
		echo '<div class="container">';
            
        echo'<div class="row">';
		echo'<div class="col-sm-4">';
		$person_idValues=populateCategory($connection,"person_id","photo");
        echo '<form id="inputs" role="form" action="displayUserChart.php" method="post">';
    
		echo'  <label for="person_idSelect">person_id:</label>';
		echo'  <select name="person_id[]" class="form-control" id="person_idSelect" form="inputs" size=10>';
		foreach($person_idValues as $person_idValue)
		{
			$thisField=strip_tags($person_idValue);
			echo'<option value="'.$person_idValue.'">'.$thisField.'</option>';
		}
		echo'  </select>';
		echo'</div>';
		
		echo'</div>';
		

		echo '<input type="submit" class="btn btn-default" value="Submit"></button> ';		
		echo '</form>';
		
		echo '</div>';
		
		$connection->close();//closes connection when you're done with it
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
		?>
</body>
</html>
