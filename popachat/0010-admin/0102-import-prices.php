<?php

$thedate=$_REQUEST['importDate'];
echo "Import Prices chosen<br>";
echo "in 500-miscellaneousFunctions.py, year is 2021<br>";
$array=import_xlsx($target_file,$try); // get imports
//------------------------------------------------------------------
// defines column to import
$colMatch=['ean'=>'J','refFour'=>'C','designation'=>'D','tva'=>'E','uniteVente'=>'E','prixAchat'=>'G','dlc'=>'K'];
$dico=importXlsArrayToDictionnary($array,$colMatch);
$primary="ean";
$tableName="prod_prices";
$filter=['ean','prixAchat','source','thedate'];
$searchKeys=['ean'];
foreach ($dico as $key=>$val){
    $dico[$key]['source']=$filename;
    $dico[$key]['thedate']=$thedate;
}

uploadIntoDatabase($dico,$colMatch,$primary,$searchKeys,$filter,$tableName,$try);
?>
