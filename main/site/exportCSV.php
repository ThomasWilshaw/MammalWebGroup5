<?php
	//sql details
	$servername="localhost";
	$username="root";
	$password="";
	$dbname="mammalweb1";
	
	//establish connection
	$connection=new mysqli($servername,$username,$password,$dbname);//establishes the sql connection
    
   /*  $select = "SELECT * FROM species";

    $export =$connection->query($select); */

    $fields = getCategories($export);

    for ( $i = 0; $i < sizeof($fields); $i++ )
    {
        $header .= $fields[i] . ",";
    }

    while( $row = mysqli_fetch_row( $export ) )
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

	
	function getCategories($connection){//returns attributes of a table as an array
		//creating and sending query
		$sql="SHOW COLUMNS FROM `animal`";  //replace "animal" with any other table part of the database initialised in the dbname variable.
		$categoryQuery=$connection->query($sql);
		//using query results
		$categoryArray=array();
		while($attribute=$categoryQuery->fetch_assoc()){
			array_push($categoryArray,$attribute['Field']);
		}
		return $categoryArray;
	}
?>