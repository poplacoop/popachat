<?php
// create dictionnary to find back ean
$refFourQuery='SELECT refFour,ean FROM prod_articles WHERE validated<2 and NOT ISNULL(refFour)';
$table=query_table($refFourQuery);
$refFourDico=create_one_field_dictionnary($table,'refFour','ean');


// list all produits that are not in alias
$listeProduitsQuery='SELECT * FROM prod_articles WHERE validated<2 and ean NOT IN (SELECT eanAlias as ean FROM prod_ean_alias)';

$countQuery="SELECT ean,count(ean) as nb FROM ($listeProduitsQuery) as A group by ean";
//echo $countQuery."<br>";
$anomalie_query="SELECT * FROM ($countQuery) as A WHERE NOT nb=1";

$table=query_table($anomalie_query);
if (sizeof($table)!=1){
    echo "Il y a ".(sizeof($table)-1)." anomalies dans la table articles (doublons)";
    displayinhtml($table);
}

$listeProduitsTable=query_table($listeProduitsQuery);

// dictionnary for all product not repeated and no aliases.
$produitEanDico=create_product_dictionnary($listeProduitsTable,"ean");

// $eanAliasDico contains ean to translate
$query="SELECT * from prod_ean_alias left outer join prod_articles on prod_ean_alias.ean=prod_articles.ean where prod_articles.validated<2";
//$query="($query) as A UNION (ean,ean from prod_articles where prod_articles.validated<2) as B";
//$query="SELECT if(isnull(ali.eanAlias),art.ean,ali.eanAlias) as eanAlias,art.ean FROM prod_articles as art join prod_ean_alias as ali on art.ean=ali.ean";
$query="SELECT * from prod_ean_alias union select ean,ean as eanAlias from prod_articles";
$table=query_table($query,0);
$eanAliasDico=create_one_field_dictionnary($table,'eanAlias','ean');

// $refFourAliasDico contains ean to translate

$query="SELECT * from prod_refFour_alias left outer join prod_articles on prod_refFour_alias.refFour=prod_articles.refFour where prod_articles.validated<2";
$query="SELECT * from prod_refFour_alias union select refFour,refFour as refFourAlias from prod_articles";
$table=query_table($query,1);
//displayinhtml($table);
$refFourAliasDico=create_one_field_dictionnary($table,'refFourAlias','refFour');
//var_dump($refFourAliasDico);
//var_dump($eanAliasDico);
$query="SELECT AL.ean,CL.id,AL.eanAlias from prod_ean_alias as AL LEFT OUTER JOIN prod_commandeList as CL ON AL.eanAlias=CL.ean WHERE NOT ISNULL(CL.ean);";
$table=query_table($query,0);
displayinhtml($table,"",0);
foreach ($table as $row){
    echo $row['eanAlias']." ";
    $query="UPDATE prod_commandeList SET ean=".$row['ean']." where id=".$row['id'];
    echo $query."<br>";
    simple_query($query);
}



?>
