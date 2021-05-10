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
    echo "ajout  d'une valeur";
    $tablekey=[];
    $tablekey['ean']=$order['ean'];
    $query="select * from prod_commandeList where commande_id=".$order['commande']." and ean='".$order['ean']."';";
    echo $query;
    $itemTable=query_table($query);
    //displayinhtml($itemTable);
    if (sizeof($itemTable)>1){
        $htmlNoInsert="<div>L'article ".$produitsDico[$tablekey['ean']]['designation']." est déjà dans la commande</div>"; 
    }
    else{
        echo "not found";
        //$tablekey['designation']=$_REQUEST['designation'];
        $tablekey['prixAchat']=$produitsDico[$tablekey['ean']]['prixAchat'];
        //$tablekey['quantite']=$_REQUEST['quantite'];
        $tablekey['commande_id']=$order['commande'];
        $filter=['ean','prixAchat','commande_id'];
        $query=create_INSERT('prod_commandeList',$tablekey,$filter,$order['commande']);
        echo $query;
        simple_query($query);
    }
}
// modification d'une valeur
if ($order['eanModif']!=""){
    echo "modification d'une valeur";
    $tablekey=[];
    $tablekey['ean']=$order['eanModif'];
    $query="select * from prod_commandeList where commande_id=".$order['commande']." and ean='".$order['eanModif']."';";
    echo $query;
    $itemTable=query_table($query);
    //displayinhtml($itemTable);
    if (sizeof($itemTable)>1){
        echo "modification";
        if ($order['quantite']!=""){
            $query="update prod_commandeList SET quantite='".$order['quantite']."' where id='".$itemTable[1]['id']."';";
            echo $query;
            simple_query($query);
        }
    }
    else{
        
        $htmlNoInsert("problem with quantite modification");
    }
}


//echo "done";
?>
