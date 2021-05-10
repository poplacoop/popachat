<?php



// Get Data
//$familleDico=create_one_field_dictionnary_sql("SELECT * FROM prod_famille",'id','titre');
//$fournisseurDico=create_one_field_dictionnary_sql("SELECT * FROM prod_fournisseur ORDER BY titre ASC",'id','titre');
$attr=["departement"=>"prod_departement","famille"=>"prod_famille","fournisseur"=>"prod_fournisseur","groupe"=>"prod_groupe"];
$dico=[];
foreach($attr as $key=>$val){
    $query="SELECT * FROM $val order by titre;";
    $table=query_table($query);
    $dico[$key]=create_one_field_dictionnary($table,"id","titre");
    $dico[$key."_rev"]=create_one_field_dictionnary($table,"titre","id");
}



?>
