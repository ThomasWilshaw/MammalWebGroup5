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
// creates an array for a animal with key as time in day and values as number
//interval of one hour
    //$animal_id = 22;
    include('config.php');
    $animal_id = $_GET["animal_id"];
    //connects to database
    $connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
    $timeArray = numberOfAnimalInHour($connection, $animal_id);

    $histoData = array();
    $totalCount = 0;
    for ($i=0; $i<24; $i++){
        $totalCount = $totalCount + intval($timeArray[$i]);
    }// calculates the total data there are
    $count = 0;
    $hourNow = 0;
    $hourValue = -1;
    $hourCount = 0;
    while($count<$totalCount){
        // for each data in the timeArray, we add it to histoData and convert the format to each apperance of an animal will have a key acompany with, instead of each key represents a hour slot. key now will not matter and the value will be the hour
        //loops for each hour and adds to the array histoData
        if ($hourValue==-1){
            $hourValue = $timeArray[0];
        }
        else if ($hourValue == $hourCount){
            $hourNow = $hourNow + 1;
            $hourCount = 0;
            $hourValue = $timeArray[$hourNow];
        }
        else{
            $histoData[$count]=$hourNow;
            $hourCount = $hourCount + 1;
            $count = $count + 1; 
        }
    }
    echo(json_encode($histoData));
    
    $connection->close();//close connection when done


    function numberOfAnimalInHour($connection, $animalid){
        $sql = "SELECT taken FROM Photo p, aggregate ag WHERE (p.photo_id=ag.photo_id) AND ag.species=".$animalid." AND (flag=167 OR flag=166);";
        $result = $connection->query($sql);
        $timeInDay = array();
        // the array has 24 keys and each key represents the starting hour e.g. for key 0, it represents the hour 0-0:59:59
        // the value of each key is updated if there are corresponding photo to each hour
        for ($i=0; $i<24; $i++){
            $timeInDay[$i]=0;
        }
        while($row=$result->fetch_assoc()){
            //returns timestamp in format yyyy-mm-dd hh:mm:ss(year,month,day,hour,month,day)
            //we just need both the digit from hours to determine what time of day is the photo taken (hour to hour)
            // if it matches, the value of array is updated to value+1

            //this checks the hour from the timestamp, and updates accordingly
            //the 11th digit of taken represents the first digit of 24hr time, 12th represents the second digit
            
            $hour = $row["taken"][11].$row["taken"][12];
            //concatenate the first and second digit of hour to create a string that represents the hour

            $value = $timeInDay[intval($hour)];
            $replacement = array();
            $replacement[intval($hour)]=$value+1;
            //creates a array with key as hour for this photo and value as the value from the $timeInDay array + 1 and we replace this into the $timeInDay array
            $timeInDay = array_replace($timeInDay, $replacement);
        }
        return($timeInDay);
    }
?>