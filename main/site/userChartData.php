
	<?php 
    //receive POST data from displayUserChart.php and edit a JSON file called "personClassified.json" with appropriate data to the userid, then the displayUserChart.php will in turn display a barchart with the users classifications

    
    
    
    
    //for a user id get chart
    //this php function generates a json file which can then be used by the barchart for the dashboard
		//sql details
        $person_id = $_POST["person_id"];
        //$person_id = 397;
    
		include('config.php');
		
		//establish connection
		$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connections

		//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
		$speciesMap=loadSpeciesMap($connection);
		
		$sitesMap=loadSitesMap($connection);
        //load the siteid to sitename list


		
		
        $totalClassifications = 0;
        $correctClassifications = 0;
        $animalArray=array();
        $animalChildren=array();
        $animalCount=0;
        
        $maxAnimal = getMaxAnimals($connection);
        $maxSites = getMaxSites($connection);
        //loops through all the possible animal
    
        $differentanimal = array();
        $animalCount = 0;
        $photoidforperson = getPersonClassified($connection, $person_id);
        $totalClassifications = sizeof($photoidforperson);
        for ($animalNumber=0; $animalNumber<=$maxAnimal; $animalNumber++){
            // loops through each type of species
            $classifiedArray = getOneAnimalFromArray($connection, $animalNumber, $photoidforperson);
            $correctClassifications = $correctClassifications + sizeof($classifiedArray);
            
            // retrieve photoids which are flagged as classified and has been classified by this person
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
        $fp = fopen('personClassified.json', 'w');
        fwrite($fp, json_encode($data));
        fclose($fp);
        $correctness = array();
        $correctness["correct"] = $correctClassifications;
        $correctness["total"] = $totalClassifications;
        echo json_encode($correctness);
        
    
    
    
    
    
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
		
        function getClassifiedAnimals($connection, $evenness, $animalid, $userid){//returns an array of photoid above an evenness and classified as this animal
            $sql = "SELECT photo_id,species FROM aggregate WHERE evenness<= AND ".$evenness.";";// selects photos where its above a threshold evenness
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
            $sql = "SELECT site_id FROM Photo;";
            $result = $connection->query($sql);
            while($row=$result->fetch_assoc()){
                if ($number<$row["site_id"]){
                    $number = $row["site_id"];
                }
            }
            return $number;
        }
    
        function getPersonClassified($connection, $person_id){// selects photos which are correctly classified for one personid
            $sql = "SELECT ag.photo_id FROM animal a, aggregate ag WHERE (a.person_id=".$person_id." AND (flag=167 OR flag=166) AND a.species=ag.species AND a.photo_id=ag.photo_id)";
            $result = $connection->query($sql);
            $photos = array();
            $countphoto = 0;
            while($row=$result->fetch_assoc()){
                $photos[$countphoto]=$row["photo_id"];
                $countphoto=$countphoto+1;
            }
            //print_r($photos);
            return $photos;
        }
        
        function getOneAnimalFromArray($connection, $animalid, $array){
            // input an array of classified photoid and animal number
            //outputs an array with photoid which are classified as this animal
            $sql = "SELECT photo_id, species FROM aggregate;";
            $result = $connection->query($sql);
            $animal = array();
            $count = 0;
            while($row=$result->fetch_assoc()){
                if ($row["species"]==$animalid){
                    // checks that if it is the same species
                    // if it is it loops through the input array to see if they are from this user
                    for ($photonum=0; $photonum<sizeof($array); $photonum++){
                        if ($row["photo_id"]==$array[$photonum]){
                            $animal[$count]=$row["photo_id"];
                            $count = $count + 1;
                        }
                    }
                }
            }
            return $animal;
        }
		
		?>
