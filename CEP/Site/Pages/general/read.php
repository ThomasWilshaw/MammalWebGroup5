<?php
    error_reporting(E_ALL);
    $path = $_REQUEST['path'];
    $txt_file = fopen($path, "r");
    $newline = "<br>";
    //$pre_open = "<pre>";
    //$pre_close = "</pre>";
    
    //echo $pre_open;
    if ($txt_file){
        while(($line = fgets($txt_file)) !== false){
            print htmlentities($line);
        }
        fclose($txt_file);
    } else{
        echo "Error.";
    }
    //echo $pre_close;
?>