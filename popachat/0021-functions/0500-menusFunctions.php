<?php 
//----------------------------------
// display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// treat request
// create a variable $order which contains $_REQUEST values or ""
function treat_request(&$order,$commands){
    foreach ($commands as $command){
        $order[$command]="";
        if (isset($_REQUEST[$command])){
            $order[$command]=$_REQUEST[$command];       
        }
    } 
}


?>
