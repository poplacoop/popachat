<?php
$commandeList="SELECT id from prod_commande WHERE fournisseur=".$_SESSION['fournisseur']." AND abs(datediff(date_livraison_prevue,'".$commandeTable[1]['date_livraison_prevue']."')) <3";
//$commandeNb=query_table($commandeList);
//displayinhtml($table);

$queryPrices="SELECT * FROM prod_commandeList WHERE commande_id in ($commandeList)";
$totalPrices="SELECT ean,sum(prixAchat*quantite) as totalAmount, sum(quantite) as quantite from ($queryPrices) as A group by ean";
$recomputedPrices="SELECT ean, totalAmount/quantite as prixAchat from ($totalPrices) as A";
$newPricesTbl=query_table($recomputedPrices,1);

$newPricesDico=create_one_field_dictionnary($newPricesTbl,'ean','prixAchat');
//displayinhtml($commandeListTable);
$init=0;
foreach ($commandeListTable as $key=>$row){
    //dispArray($row);
    if($init==1){
        //echo $row['ean'];
        $commandeListTable[$key]['prixAchat']=$newPricesDico[$row['ean']];
    }
    else{
        $init=1;
    }
    
}

/*
$commandQuery="SELECT LIST.*,ARTI.designation,ARTI.tri,ARTI.refFour,ARTI.departement, ARTI.famille, ARTI.fournisseur, 
    ARTI.tva,ARTI.conditionnement,ARTI.contenance, ARTI.uniteContenance, ARTI.uniteVente,ARTI.validated FROM
               (SELECT * FROM prod_commandeList WHERE commande_id=$commandeId order by ean) AS LIST         
               LEFT OUTER JOIN 
               (SELECT * FROM (SELECT * FROM prod_articles where validated<2 order by ean) as prod_articles ) AS ARTI
               ON LIST.ean=ARTI.ean $queryTri";
    
    $commandeListTable=query_table($commandQuery);
    */ 
?>
