<?php
// creates an array for a animal with key as month and appearances as number
//interval of one hour
    //$animal_id = 22;
    include('config.php');
    $animal_id = $_POST["animal_id"];
    //connects to database
    $connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);
    $timeArray = numberOfAnimalInHour($connection, $animal_id);

    $histoData = array();
    $totalCount = 0;
    for ($i=0; $i<12; $i++){
        $totalCount = $totalCount + intval($timeArray[$i]);
    }// calculates the total data there are
    $count = 0;
    $monthNow = 0;
    $monthValue = -1;
    $monthCount = 0;
    while($count<$totalCount){
        // for each data in the timeArray, we add it to histoData and convert the format to each apperance of an animal will have a key acompany with, instead of each key represents a month slot. key now will not matter and the value will be the hour
        //loops for each month and adds to the array histoData
        if ($monthValue==-1){
            $monthValue = $timeArray[0];
        }
        else if ($monthValue == $monthCount){
            $monthNow = $monthNow + 1;
            $monthCount = 0;
            $monthValue = $timeArray[$monthNow];
        }
        else{
            $histoData[$count]=$monthNow;
            $monthCount = $monthCount + 1;
            $count = $count + 1; 
        }
    }
    echo(json_encode($histoData));
    
    $connection->close();//close connection when done


    function numberOfAnimalInHour($connection, $animalid){
        $sql = "SELECT taken FROM Photo p, aggregate ag WHERE (p.photo_id=ag.photo_id) AND ag.species=".$animalid." AND (flag=167 OR flag=166);";
        $result = $connection->query($sql);
        $timeInDay = array();
        // the array has 12 keys and each key represents a month month 0-1 = december1st to 31st
        // the value of each key is updated if there are corresponding photo to each month
        for ($i=0; $i<12; $i++){
            $timeInDay[$i]=0;
        }
        while($row=$result->fetch_assoc()){
            //returns timestamp in format yyyy-mm-dd hh:mm:ss(year,month,day,hour,month,day)
            //we just need both the digit from month
            // if it matches, the value of array is updated to value+1

            //this checks the hour from the timestamp, and updates accordingly
            //the 5th digit of taken represents the first digit of month, 6th represents the second digit
            
            $hour = $row["taken"][5].$row["taken"][6];

            //concatenate the first and second digit of month to create a string that represents the month
            
            
            //if december month->0 for easier representation
            if (intval($hour)==12){
                $hour = 0;
            }
            
            
            $value = $timeInDay[intval($hour)];
            $replacement = array();
            $replacement[intval($hour)]=$value+1;
            //creates a array with key as month for this photo and value as the value from the $timeInDay array + 1 and we replace this into the $timeInDay array
            $timeInDay = array_replace($timeInDay, $replacement);
        }
        return($timeInDay);
    }



?>