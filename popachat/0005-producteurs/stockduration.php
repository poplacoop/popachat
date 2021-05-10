<?php
@session_start();
include "0000-initFilesProd.php";
include "0500-listFunctions.php";
//include "0501-graphFunctions.php";
include "0503-stockFunctions.php";



$myRange=4;
$dicoStock=getstockInformationDico($myRange);
//dispArray($dicoStock['3176800001268']);

$table=getstockInformation($myRange);
exportToXls("jourdestock.xls",$table);
echo "<a href='./files/jourdestock.xls'>jourdestock.xls</a>";
displayinhtml($table);
   

  

?>
