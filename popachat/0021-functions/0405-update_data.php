<?php
@session_start();
include("0501-retrieveFunctions.php");
include("0505-miscellaneousFunctions.php");
$query=$_REQUEST['query'];
simple_query($query);
echo $query."<br>";
echo "ENREGISTRE! Merci!";

?>
