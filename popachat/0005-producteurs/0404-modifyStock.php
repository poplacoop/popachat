<?php
@session_start();
require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0505-miscellaneousFunctions.php";
include "../0021-functions/0501-retrieveFunctions.php";
include "0500-listFunctions.php";
include "0003-prepareData.php";

//-----------------------------------------------------------------------
// Insert New Item in commandeList
$htmlNoInsert="";
// ajout d'une valeur
if ($order['ean']!=""){
    $query="select max(thedate) as thedate from prod_stock where ean='".$order['ean']."'";
    $query="select id,stock from prod_stock where thedate in ($query)  and ean='".$order['ean']."' and source='manuel'";
    $table=query_table($query);
    if (sizeof($table)==2){
        $query="update prod_stock set stock=".$order['stock']." where id=".$table[1]['id'];
    }
    else{
        $query="insert into prod_stock (thedate,ean,stock,source,author)VALUES('".date("Y-m-d")."','".$order['ean']."','".$order['stock']."','manuel','".$_SESSION['userInfo']['userId']."')";
    }
    echo $query;
    simple_query($query);
}


//echo "done";
?>
