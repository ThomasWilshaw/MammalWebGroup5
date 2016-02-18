<html>
<head>
	<title>Php x SQL</title>
</head>
<body>
	<a href="#">Back</a>  <!--  MAKE THIS POINT BACK TO THE SEARCH pAGE  -->
	<?php 

	//At the moment, three searches are hard coded into the page. Once we know the input format from the search form, change to only output a single table that includes all the fields being searched for.

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

		//TABLE 1 - High unevenness results from aggregate

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

		//TABLE 2 - Results from aggregate which are not unanimous or useless, but also ARE within the unevenness threshold

		$sql="SELECT * FROM aggregate WHERE evenness>=0 AND evenness<".$uneventhreshold.";";
		$even=$connection->query($sql);

		echo "<br><br><h2>Good results:</h2><br>";

		echo "<table border='1'>";
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

		//TABLE 3 - Results from photo that were uploaded between april 11 15 and the current date
		
		searchBetweenDates($connection,date("2015-04-11 23:10:01"),date("Y-m-d H:i:s"));

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
		?>
	</table>
</body>
</html>