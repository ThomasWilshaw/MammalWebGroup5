<html>
<head>
	<title>Php x SQL</title>
</head>
<body>
	<?php 
		$servername="localhost";
		$username="root";
		$password="toot";
		$dbname="mammalwebdump";

		$connection=new mysqli($servername,$username,$password,$dbname);

		$speciesmap=loadSpeciesMap($connection);
		//Get species list
		
		echo print_r($speciesmap);

		//Echos photo id for all photos whose unevenness> some threshold ie all the photos that need to be assessed by scientist
		$uneventhreshold=0.75;
		$sql="SELECT * FROM aggregate WHERE evenness>".$uneventhreshold.";";
		$uneven=$connection->query($sql);

		echo "<h2>High unevenness results:</h2><br>";

		if($uneven->num_rows>0){
			while($row=$uneven->fetch_assoc()){
				echo "Photo ID: ".$row["photo_id"]."<br>";
			}
		}
		else{
			echo "No uneven results";
		}

		//Selects the photo ids for useful photos

		$sql="SELECT * FROM aggregate WHERE evenness>=0 AND evenness<".$uneventhreshold.";";
		$even=$connection->query($sql);

		echo "<br><br><h2>Good results:</h2><br>";

		if($even->num_rows>0){
			while($row=$even->fetch_assoc()){
				echo "Photo ID: ".$row["photo_id"]."<br>";
			}
		}
		else{
			echo "No useful results";
		}

		$connection->close();

		function loadSpeciesMap($connection){
			$sql="SELECT option_id,option_name FROM options WHERE struc='bird' OR struc='mammal' OR struc='noanimal'";
			$speciesquery=$connection->query($sql);

			$speciesmap=array();
			while($row=$speciesquery->fetch_assoc()){
				$speciesmap[$row["option_id"]]=$row["option_name"];
			}
			return $speciesmap;
		}
		?>
</body>
</html>