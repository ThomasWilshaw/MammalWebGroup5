<?php
	
	include('config.php');
		
    //establish connection
        $connection=new mysqli(DBHOST,DBUSER,DBPASS,DBNAME);//establishes the sql connection
	
	//The results of this sql query will be output to a csv file
	$sql="SELECT photo_id FROM `animal`;";
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
	
	$connection->close();

	
	function getCategories($connection){//returns attributes of a table as an array
		//creating and sending query
		$sql="SHOW COLUMNS FROM `animal`";	//replace "animal" with any other table part of the database initialised in the dbname variable.
		$categoryQuery=$connection->query($sql);
		//using query results
		$categoryArray=array();
		while($attribute=$categoryQuery->fetch_assoc()){
			array_push($categoryArray,$attribute['Field']);
		}
		return $categoryArray;
	}
?>