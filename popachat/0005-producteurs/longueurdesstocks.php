<?php
@session_start();
require_once "0000-initFilesProd.php";
require_once "0500-listFunctions.php";
include "../0021-functions/0409-generateDico.php";
//include "0501-graphFunctions.php";
require_once "0503-stockFunctions.php";
$myRange=4;

$table=getstockInformation($myRange);

//----------------------------------------------------
    // create Excel and html
    //include "../0021-functions/0506-exportToXls.php";
    $curDate=date("Y-m-d");
    $str.="<a href='./files/dureeDesStocks_$curDate.xls'>dureeDesStocks_$curDate.xls</a>";
    $str.=exportToXls("dureeDesStocks_$curDate.xls",$table,[]);
    
    //echo $str;
//}
echo "end longueur des stocks";
?>
