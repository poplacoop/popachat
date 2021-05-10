<?php

$thedate=$_REQUEST['importDate'];
echo "Import Articles dans la base<br>";
echo "in 500-miscellaneousFunctions.py, year is 2021<br>";

//------------------------------------------------------------------
// get File and save it
//------------------------------------------------------------------

    $target_dir = "./files/";
    $target_file = $target_dir . basename($_FILES["importFile"]["name"]);

    move_uploaded_file($_FILES["importFile"]["tmp_name"], $target_file);
    $filename=$_FILES["importFile"]["name"];
    echo $filename."<br><br>";


$try=0;
//$array=import_xlsx($target_file,$try); // get imports
$array=import_xls($target_file,$try,"xls"); // get imports
//------------------------------------------------------------------
// defines column to import
$colMatch=['ean'=>'A','refFour'=>'C','designation'=>'D','departement'=>'E',];
$colMatch=array_merge($colMatch,['famille'=>'F','conditionnement'=>'G','contenance'=>'H','uniteContenance'=>'I','prixAchat'=>'J','prixVente'=>'K','tva'=>'L','founisseur'=>'N','uniteVente'=>'W']);
$dico=importXlsArrayToDictionnary($array,$colMatch);

// import articles
echo "<h1>Importer les articles format AEMSOFT</h1>";
$primary="ean";
$tableName="prod_articles";
$filter=array_keys($colMatch);
$filter=array_merge($filter,['author']);
$searchKeys=['ean'];
foreach ($dico as $key=>$val){
    $dico[$key]['author']=$_SESSION['userInfo']['userId'];
    $dico[$key]['thedate']=$thedate;
}
uploadIntoDatabase($dico,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'ean');

/*
// import prices
echo "<h1>Importer les Prix</h1>";
$filter=['ean','prixAchat','prixVente','source','thedate','author'];
$tableName='prod_prices';
importFromTableKeys($dico,$tableName,$try,$primary,$searchKeys,$filter,'prixAchat');
//uploadIntoDatabase($dico,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'prixAchat');


// import stocks
$filter=['ean','stock','source','thedate','author'];
$tableName='prod_stock';
echo "<h1>Importer les Stock</h1>";
uploadIntoDatabase($dico,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'stock');
*/
?>
