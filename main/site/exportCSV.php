<?php
    $username = "root";
    $password = "toot";
    
    $conn = mysql_connect("localhost", $username, $password);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }else{
        echo "Connected successfully";
    }
    
    mssql_select_db('mammalweb1', $conn);
    
    $select = "SELECT * FROM species";

    $export = mysql_query ( $select ) or die ( "Sql error : " . mysql_error( ) );

    $fields = mysql_num_fields ( $export );

    for ( $i = 0; $i < $fields; $i++ )
    {
        $header .= mysql_field_name( $export , $i ) . ",";
    }

    while( $row = mysql_fetch_row( $export ) )
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

?>