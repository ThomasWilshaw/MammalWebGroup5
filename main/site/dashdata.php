<?php
/*
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
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
//This php script generates a json object mapping confirmed photos of animals to the sites they were taken at for display as a barchart on scientistDashboard
include('config.php');

//establish connection
$connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connections

//Get species list - $speciesMap holds an associative array of number(int)->species(string) as found in the options table
$speciesMap=loadSpeciesMap($connection);

$sitesMap=loadSitesMap($connection);
//load the siteid to sitename list

$differentanimal = array();
$animalCount = 0;

foreach ($speciesMap as $animalNumber=>$animalName){
    // loops through each type of species
    //echo $animalName.":\t\t";
    $classifiedArray = getClassifiedAnimals($connection, $animalNumber);
    //echo "<br>".$animalName."--->".print_r($classifiedArray)."<br><br>";
    if (count($classifiedArray)>0){//make sure there is at least one classification
        $animalSites = getArrayOfSites($connection, $classifiedArray);
        //using the photoid retrieved above, we use this to go into the main database and find corresponding siteid and turn it into an array with key as photoid and value as siteid
        $animalC = array();
        $animalChildren = array();
        $animalChildrenSize = 0;
        foreach ($sitesMap as $siteNumber=>$siteName){
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
                $siteNameWithNumber=$siteName . " " . strval($siteNumber);
                $animalInThisSite["name"]=$siteNameWithNumber;// add to the animal array with children of different sites
               
                $animalInThisSite["size"]=$sizeInSite;
                $animalChildren[$animalChildrenSize]=$animalInThisSite;
                $animalChildrenSize = $animalChildrenSize + 1;
            }
        }
        $animalC["name"]=$animalName;//for each array, name is the animal
        $animalC["children"]=$animalChildren;// for the array with all animals, this array has children with different animal
        $differentanimal[$animalCount]=$animalC;
        $animalCount += 1;
    }
}
$data = array();
$data["name"]="Animal";
$data["children"]=$differentanimal;// forms the array with all animals with children of different sites with children of their photoid

echo json_encode($data);

$connection->close();//closes connection when you're done with it


function loadSpeciesMap($connection){
	$sql="SELECT option_id,option_name FROM options WHERE struc='bird' or struc='mammal' or struc='notinlist'";  
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

function getClassifiedAnimals($connection, $animalid){//returns an array of photoid above an evenness and classified as this animal
    $sql = "SELECT photo_id FROM aggregate WHERE (flag=166 OR flag=167) AND species=".$animalid.";";// selects photos where its above a threshold evenness
    $photoidquery = $connection->query($sql);
    $photoidmap = array();
	while($row=$photoidquery->fetch_assoc()){
        $photoidmap[]=$row["photo_id"];
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
    //echo print_r($animalSite)."<br>";
    return $animalSite;
}
?>