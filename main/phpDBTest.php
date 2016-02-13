<html>
<head>
	<title>Php x SQL</title>
</head>
<body>
	<a href="#">Back</a>  <!--  MAKE THIS POINT BACK TO THE SEARCH pAGE  -->
	<?php 
		$servername="localhost";
		$username="root";
		$password="toot";
		$dbname="mammalwebdump";

		$connection=new mysqli($servername,$username,$password,$dbname);

		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesmap=loadSpeciesMap($connection);		

		//Echos photo id for all photos whose unevenness> some threshold ie all the photos that need to be assessed by scientist
		$uneventhreshold=0.75;
		$sql="SELECT * FROM aggregate WHERE evenness>".$uneventhreshold.";";
		$uneven=$connection->query($sql);

		echo "<h2>High unevenness results:</h2><br>";

		//This is an easy way to structure the output table, have some string combination thing for all the passed ion variables (from dropdowns) that define the columns as well as for the SQL queries
		echo "<table>";
		echo "<tr><td>Photo ID</td></tr>";
		if($uneven->num_rows>0){
			while($row=$uneven->fetch_assoc()){
				echo "<tr>";
				echo "<td>".$row["photo_id"]."</td>";
				echo "</tr>";
			}
		}
		else{
			echo "<tr><td>No uneven results</td></tr>";
		}
		echo "</table>";

		//Selects the photo ids for useful photos
		$sql="SELECT * FROM aggregate WHERE evenness>=0 AND evenness<".$uneventhreshold.";";
		$even=$connection->query($sql);

		echo "<br><br><h2>Good results:</h2><br>";

		echo "<table>";
		echo "<tr><td>Photo ID</td><td>Species</td></tr>";

		if($even->num_rows>0){
			while($row=$even->fetch_assoc()){
				echo"<tr>";
				echo "<td>".$row["photo_id"]."</td>";
				echo "<td>".$speciesmap[$row["species"]]."</td>";
				echo"</tr>";
			}
		}
		else{
			echo "<tr><td>No useful results</td></tr>";
		}

		echo "</table>";

		$connection->close();

		function loadSpeciesMap($connection){
			$sql="SELECT option_id,option_name FROM options WHERE struc='bird' OR struc='mammal' OR struc='noanimal' OR struc='notinlist'";  //Really not happy with this line
			$speciesquery=$connection->query($sql);

			$speciesmap=array();
			while($row=$speciesquery->fetch_assoc()){
				$speciesmap[$row["option_id"]]=$row["option_name"];
			}
			return $speciesmap;
		}
		?>
	</table>
</body>
</html>