<?php
@session_start();
require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0505-miscellaneousFunctions.php";
include "../0021-functions/0501-retrieveFunctions.php";
include "0500-listFunctions.php";
include "0003-prepareData.php";
include "0501-graphFunctions.php";

//----------------------------------------------------------------------
// Get Data

$designation=$produitsDico[$order['ean']]['designation'];
echo plot_chart_stocks($order['ean'],$designation,$order);

?>
