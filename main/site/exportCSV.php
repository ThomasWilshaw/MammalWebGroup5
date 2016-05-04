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
	
	include('config.php');
	
	set_time_limit(120);
	//sets a 2 minute timeout 
	

		
    //establish connection
    $connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection
	
	makeSecureForSQL($connection);//see below
	
	//The results of this sql query will be output to a csv file
    if(isset($_GET["data"])){
			$sqlData=$_GET["data"];
			echo $sqlData;
	   
		if(!isset($_REQUEST['mode'])){
			$_REQUEST['mode']="1";
		}
		$mode=$_REQUEST['mode'];
		if(($mode=="1") or ($mode=="2")){
			$query="SELECT * FROM aggregate INNER JOIN photo ON aggregate.photo_id=photo.photo_id INNER JOIN site ON photo.site_id=site.site_id WHERE ";
		}
		else{
			$query="SELECT * FROM site ";
		}
		$sql=$query.$sqlData;
	 }
    else{
    	$sql="SELECT photo_id FROM `animal` WHERE ";
    }
	echo $sql;
	$results=$connection->query($sql);
	$fields =  mysqli_fetch_fields($results);
	
	$header="";

	foreach($fields as $val){
		$header .= $val->name . ",";
	}

	$data="";
	while( $row = mysqli_fetch_row( $results ) )
	{
		$line = '';
		foreach( $row as $value )
		{											 
			if ( ( !isset( $value ) ) || ( $value == "" ) )
			{
				$value = ",";
			}
			else
			{
				$value = str_replace( '"' , '""' , $value );
				$value = '"' . $value . '"' . ",";
			}

			$line .= $value;
		}
		$data .= trim( $line ) . "\n";
	}
	$data = str_replace( "\r" , "" , $data );

	if ( $data == "" )
	{
		$data = "\n(0) Records Found!\n";						 
	}

	header("Expires: 0");
	header("Cache-control: private");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Description: File Transfer");
	header("Content-Type: application/vnd.ms-excel");
	header("Content-disposition: attachment; filename=export.csv");
	print "$header\n$data";
	
	
		/////////////////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////////////////////////
	/*this function will be used to make sure every variable set in the $_REQUEST array doesn't contain
	injected SQL. It doesn't need to operate on the keys of the array, only the values, as only specific
	keys will be used in the arrayToQuery function to generate the SQL query.
	The $connection parameter is included because the mysqli real_escape_string() function takes this
	as a parameter to see which characters are acceptable*/
	function makeSecureForSQL($myConnection){
		//parses through everything in $_REQUEST
		foreach($_GET as $key=>$value)
		{
			if(is_array($value)){
				foreach($value as $valueKey=>$valueValue)
				{
					//escapes any character that may enable an sql injection attack
					$value[$valueKey]=$myConnection->real_escape_string($valueValue);
				}
			}
			else{
				//escapes any character that may enable an sql injection attack
				$_GET[$key]=$myConnection->real_escape_string($value);
			}
		}
	}
		
	$connection->close();
?>