<?php
// Get Data
//$familleDico=create_one_field_dictionnary_sql("SELECT * FROM prod_familles",'id','titre');
//$fournisseurDico=create_one_field_dictionnary_sql("SELECT * FROM prod_fournisseur ORDER BY titre ASC",'id','titre');

// dico departement Famille
$query="SELECT id,departement from prod_famille";
$table=query_table($query);
//displayinhtml($table);
$dico_fam_dep=create_one_field_dictionnary($table,'id','departement');
//echo "dico";
//var_dump($dico_fam_dep);
//die;
$attr=["departement"=>"prod_departement","famille"=>"prod_famille","fournisseur"=>"prod_fournisseur"];
$dico=[];



//departement famille fournisseur
$attr=["departement"=>"prod_departement","famille"=>"prod_famille",
"fournisseur"=>"prod_fournisseur","groupe"=>"prod_groupe"];
$dico=[];
foreach($attr as $key=>$val){
    $query="SELECT * FROM $val order by id;";
    $table=query_table($query);
    $dico[$key]=create_one_field_dictionnary($table,"id","titre");
}

foreach($attr as $key=>$val){
    $query="SELECT * FROM $val order by titre;";
    $table=query_table($query);
    $dico[$key."alf"]=create_one_field_dictionnary($table,"id","titre");
}

foreach($attr as $key=>$val){
    $query="SELECT * FROM $val order by id desc;";
    $table=query_table($query);
    $dico[$key."desc"]=create_one_field_dictionnary($table,"id","titre");
}

foreach($attr as $key=>$val){
    $query="SELECT * FROM $val order by titre desc;";
    $table=query_table($query);
    $dico[$key."alf"."desc"]=create_one_field_dictionnary($table,"id","titre");
}

// All products
$listeAllProduitsQuery="SELECT * FROM (SELECT * FROM prod_articles where validated<2) as prod_articles";
//echo $listeProduitsQuery;
$listeAllProduitsTable=query_table($listeAllProduitsQuery);
$produitsDico=create_product_dictionnary($listeAllProduitsTable,'ean','designation');

?>
