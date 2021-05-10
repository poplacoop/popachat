<?php
@session_start();
require_once "0000-initFilesProd.php";
require_once "0500-listFunctions.php";
//include "0501-graphFunctions.php";
//require_once "0503-stockFunctions.php";
if (isset($trial)){
    require_once "0501trial-graphFunctions.php";
}
else{
    require_once "0501-graphFunctions.php";
}

function evaluateRupture($order,$ean=""){
    //------------------------------------------------------------------
    // finds the days when there is a sale  
    // $avgOfSales: plu on days of sales when there is stock
    if (isset($ean)){$where= " and ean=".$order['ean'];}else{$where = "";}
    
    
    $query="SELECT thedate,sum(cattc) as cattc FROM `prod_plu` where thedate > curdate()-interval 30 day  group by thedate   ORDER BY sum(cattc) ASC ";
    $listeActiveDays="SELECT thedate from ($query) as A where cattc >1000"; // list all active days in last 30 days
    $daysOfSales=query_table_dico($listeActiveDays);
    
    //------------------------------------------------------------------
    // find average of sales on days of sale
    
    $query="SELECT ean,quantite*(caht-prixAchat*quantite) as beneficeHt FROM prod_plu WHERE thedate in ($listeActiveDays) $where"; // get plu for products in active days
    $queryRupture="SELECT *,avg(beneficeHt) as beneficeHtAvg FROM ($query) as A group by ean"; //makes average for products
    $avgOfSales=query_table($queryRupture);
    
    // loop over sales
    $ruptureStock=[];
    if (sizeof($avgOfSales)>1){
        // $salesAmount is the average of the sales on the active days.
        $salesAmount=$avgOfSales[1]["beneficeHtAvg"];
        //echo $salesAmount;
        //displayinhtml($avgOfSales);
        //--------------------------------------------------------------
        // retrieve 0 stock
        
        
        $data=getStockValue($order);
        //dispArray($data['stock']);
        $ruptureDays=0;
        $total=0;
        foreach($daysOfSales as $row){
            if (isset($data['stock'][$row['thedate']])){
                if (($data['stock'][$row['thedate']])<=0){
                    //echo $row['thedate']." ";
                    $ruptureDays+=1;
                }
            }
            $total+=1;
        }
        $ruptureStock[$order['ean']]['nombreDeJours']=$ruptureDays;
        $ruptureStock[$order['ean']]['manqueAGagner']=$ruptureDays*$salesAmount/($total-$ruptureDays)*$total;
        //echo $ruptureDays; 
        
        //echo "We lost".$lostAmount." euros";
    }
    
    return $ruptureStock;
}    
  

?>
