<!DOCTYPE html>
<html>
<body>
<?php
    error_reporting(E_ALL);
	$fileLocation=$_REQUEST['fileLocation'];
    $text = $_REQUEST['textData'];
    $txt_file = fopen($fileLocation, "w");

    
    if ($txt_file){
        fwrite($txt_file,$text);
		fclose($txt_file);
    } else{
        echo "Error.";
    }
?>
</body>
</html>