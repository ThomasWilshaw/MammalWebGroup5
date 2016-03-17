<!DOCTYPE html>
<html>


<body>

	<?php 
    

    //this php function generates a json file which can then be used by the barchart for the dashboard
		//sql details
    
    
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connections
		


		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);
		
		$sitesMap=loadSitesMap($connection);
        //load the siteid to sitename list


		
		

        $animalArray=array();
        $animalChildren=array();
        $evenness = 0.5;
        $animalCount=0;
        
        $maxAnimal = getMaxAnimals($connection);
        $maxSites = getMaxSites($connection);
        //loops through all the possible animal
    
        $differentanimal = array();
        $animalCount = 0;
        for ($animalNumber=0; $animalNumber<=$maxAnimal; $animalNumber++){
            // loops through each type of species
            $classifiedArray = getClassifiedAnimals($connection, $evenness, $animalNumber);
            // retrieve data >= a certain evenness, data is the photoid
            if (count($classifiedArray)>0){//make sure there is at least one classification
                $animalSites = getArrayOfSites($connection, $classifiedArray);
                //using the photoid retrieved above, we use this to go into the main database and find corresponding siteid and turn it into an array with key as photoid and value as siteid
                $animalC = array();
                $animalChildren = array();
                $animalChildrenSize = 0;
                for ($siteNumber=0; $siteNumber<=$maxSites; $siteNumber++){
                    //loops through each siteid and see if it exists in the array with photoid and siteid, if it does, we use the photoid and make it into an array of sites with children of the photoid which belongs to the site
                    if (in_array($siteNumber, $animalSites)){
                        $arrayPhotoID = array_keys($animalSites);
                        $arraySites = array_values($animalSites);
                        $animalInThisSiteChildren = array();
                        $numberInThisSite = 0;
                        $sizeInSite = 0;
                        for ($i=0; $i<sizeof($arrayPhotoID); $i++){//loops through the array for this species in this site
                            if ($arraySites[$i]==$siteNumber){
                                $sizeInSite = $sizeInSite + 1;
                            }
                        }
                        $animalInThisSite = array();
                        $siteName=$sitesMap[$siteNumber];
                        $siteNameWithNumber=$siteName . " " . strval($siteNumber);
                        $animalInThisSite["name"]=$siteNameWithNumber;// add to the animal array with children of different sites
                       
                        $animalInThisSite["size"]=$sizeInSite;
                        $animalChildren[$animalChildrenSize]=$animalInThisSite;
                        $animalChildrenSize = $animalChildrenSize + 1;
                    }
                }
                $animalName = $speciesMap[$animalNumber];
                $animalC["name"]=$animalName;//for each array, name is the animal
                $animalC["children"]=$animalChildren;// for the array with all animals, this array has children with different animal
                $differentanimal[$animalCount]=$animalC;
                $animalCount = $animalCount + 1;
            }
        }
        $data = array();
        $data["name"]="Animal";
        $data["children"]=$differentanimal;// forms the array with all animals with children of different sites with children of their photoid
        $fp = fopen('classified.json', 'w');
        fwrite($fp, json_encode($data));
        fclose($fp);
    
    
    
    
    
        $connection->close();//closes connection when you're done with it
        
    
    

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
    
        // returns an array with key as siteid and value as sitename
        function loadSitesMap($connection){
            $sql = "SELECT site_id, site_name FROM Site";
            $sitesquery=$connection->query($sql);
            $sitesmap = array();
            while($row=$sitesquery->fetch_assoc()){
                $sitesmap[$row["site_id"]]=$row["site_name"];
            }
            return $sitesmap;
        }
		
        function getClassifiedAnimals($connection, $evenness, $animalid){//returns an array of photoid above an evenness and classified as this animal
            $sql = "SELECT photo_id,species FROM aggregate WHERE (flag=167 OR flag=166);";// selects photos where its above a threshold evenness
            $photoidquery = $connection->query($sql);
            $photoidmap = array();
			while($row=$photoidquery->fetch_assoc()){
                if (intval($animalid)==intval($row["species"])){
                    $photoidmap[$row["photo_id"]]=$row["photo_id"];
                }
            }
            return $photoidmap;
        }
    
    
        function getArrayOfSites($connection, $array){//returns an array of sites with keys corresponding to photo_id
            $sql = "SELECT photo_id, site_id FROM Photo;";
            $result = $connection->query($sql);
            $animalSite = array();
            while($row=$result->fetch_assoc()){
                $photoID = $row["photo_id"];
                if (array_key_exists($photoID, $array)){
                    $animalSite[$photoID]=$row["site_id"]; 
                }

            }
            return $animalSite;
        }
    
        function getMaxAnimals($connection){//returns the max number of animalid
            $sql = "SELECT option_id FROM Options;";
            $result = $connection->query($sql);
            $maxAnimal = 0;
            while($row=$result->fetch_assoc()){
                if ($maxAnimal<$row["option_id"]){
                    $maxAnimal = $row["option_id"];
                }
            }
            return $maxAnimal;
        }
    
        function getMaxSites($connection){//returns the maximum number of sites
            $number = 0;
            $sql = "SELECT site_id FROM PHOTO";
            $result = $connection->query($sql);
            while($row=$result->fetch_assoc()){
                if ($number<$row["site_id"]){
                    $number = $row["site_id"];
                }
            }
            return $number;
        }
		
		?>
</body>
</html>