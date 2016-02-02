<?php
//This one is designed to be used with GET, rather than load. Returns a json object.
    error_reporting(E_ALL);
    $path = $_REQUEST['path'];
	$linesArray = file($path,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);//json object representing array - each element a line
	$returnObject=json_encode($linesArray);
	echo $returnObject;	
?>
