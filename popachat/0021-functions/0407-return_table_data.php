<?php
@session_start();
include("0501-retrieveFunctions.php");
include("0505-miscellaneousFunctions.php");
$query=$_REQUEST['query'];
$table=query_table($query,0);
foreach ($table as $row){
    echo "<tr><td>".$row['refFourAlias']."</td></tr>";
}


?>
