<?php
require '../0022-vendor/autoload.php';
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
//include "../0021-functions/0410-getNewEan.php";
$disp=0;
$userId=$_SESSION['userInfo']['userId'];
$str="";
//----------------------------------------------------------------------
// Choose format and prepare files
//----------------------------------------------------------------------
$fournisseurParam=[];
$rowNb=[];
$colNb=[];

//$str.=$format;
$sql="SELECT fichierimport,colisSheetName from prod_fournisseur WHERE id='".$order['fournisseur']."';";
$table=query_table($sql);
//----------------------------------------------------------------------
// fichier import fournisseur
$fichierCommandeFournisseur=$table[1]['fichierimport'];
$fichierColisSheetName=$table[1]['colisSheetName'];
$outputFileName=$outputFileNameRoot.".xls";
$fullOutputFileName = './files/'.$outputFileName;


//----------------------------------------------------------------------
//fichier format fournisseur
// load format.
//$format=$table[1]['formatXLS'];


$sql="SELECT * from prod_import_readxls WHERE format in (select format from prod_import_fournisseurs where fournisseur='".$order['fournisseur']."');";
$table=query_table($sql,1);
$format=$table[1]['format'];
if ($_SESSION['userInfo']['admin']){
    $str.= "Numéro du format: ".$format;
    //displayinhtml($table,"",1);
    //var_dump($table);
}
array_shift($table);
$keysList=[];  // list of variables in prod_import_readxls for this file
foreach ($table as $val){
    //print_r($val)."<br>";
    //$str.=$val['keyword'];
    $rowNb[$val['keyword']]=$val['row'];
    $colNb[$val['keyword']]=ord($val['col'])-ord('A');
    $colLetter[$val['keyword']]=$val['col'];
    //mp($val['keyword']."=(".$rowNb[$val['keyword']].",".$colNb[$val['keyword']].")",$disp);
    array_push($keysList,$val['keyword']);
}

//dispArray($keysList);
//dispArray($colNb);
//dispArray($rowNb);
//$prodArticleKeyList=["tva","ean","prixAchat","refFour","designation","conditionnement"];

//  intersection of prod_import_readxls with $prodArticleKeyList (to be defined).
//$prodArticleKeyListThisInput=array_intersect($prodArticleKeyList,$keysList);



//var_dump($refFourAliasDico);
//----------------------------------------------------------------------
// Read the xls file
//----------------------------------------------------------------------
$fullFileCommandeFournisseur="./files/".$fichierCommandeFournisseur;
$workBook = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullFileCommandeFournisseur);

if ($fichierColisSheetName!=""){
    $sheet = $workBook->getSheetByName($fichierColisSheetName); // Choose first sheet....
}
else{
    $sheet = $workBook->getSheet(0);
}
// display head of excell...
$str.="Le fichier $fullFileCommandeFournisseur a été chargé<br>";
if ($fichierColisSheetName!=""){
    $str.= "La feuille à remplir est ".$fichierColisSheetName;
}


// Store data from the activeSheet to the variable in the form of Array
//$data = array(1,$sheet->toArray("A1:B5",true,true,true)); 
//ini_set ("memory_limit", '512M');
//$data = array(1,$sheet->toArray()); 
//$sht=$data[1]; 
//var_dump($sht);
//var_dump($colLetter);
//$str.="sizeof data".sizeof($data);  
//$str.="coucou";
// Display the sheet content 
array_shift($commandeListTable);
//-----------------------------------------------------
// Select column where ean is.
if (isset($colLetter['ean'])){
    $cellRange=$colLetter['ean']."1:".$colLetter['ean']."5000";
    // list is the list of the ean in the column
    $listEan=$sheet->rangeToArray($cellRange,0,NULL,TRUE,TRUE,TRUE);
    //echo sizeof($list);
    //var_dump($listEan);
    // create list of ean of the column
    foreach ($listEan as $key=>$val){
        $listEan[$key]=$val[$colLetter['ean']];
    }
}
//-----------------------------------------------------
// Select column where refFour is.
if (isset($colLetter['refFour'])){
    $cellRange=$colLetter['refFour']."1:".$colLetter['refFour']."5000";
    //echo $cellRange;
    // list is the list of the ean in the column
    $listRefFour=$sheet->rangeToArray($cellRange,0,NULL,TRUE,TRUE,TRUE);
    //echo sizeof($list);
    //var_dump($listEan);
    // create list of ean of the column
    foreach ($listRefFour as $key=>$val){
        $listRefFour[$key]=$val[$colLetter['refFour']];
        if ($listRefFour[$key]==""){$listRefFour[$key]="Empty";}
    }
}



//displayinhtml($commandeListTable);
//var_dump($commandeListTable);


//echo ($listEan[13]);
//$listRefFour=[];
$listRefFour[0]="toto";
$listRefFour[1]="toutout";
$listRefFour[2]="SG-COTOMNAT701";
$listRefFour[3]="tata";
$listRefFour[5]="titi";
//var_dump($listRefFour[2416]);
//var_dump($listRefFour);
//echo "search:".array_search("SG-COTOMNAT700",$listRefFour);


//echo "<br>start<br>";
foreach($commandeListTable as $row){
    $eanRow="";
    //echo "<br>".$row['ean']."<br>";
    //dispArray($row);
    if (isset($colLetter['ean'])){
        $ean=$row['ean'];
        //echo $ean."<br>";
        //echo "search:".array_search($ean,$listEan)."=<br>";
        $eanRow=array_search($ean,$listEan);
    }
    if ($eanRow==""){
        //echo "ean failed";
        if (isset($colLetter['refFour'])){
            $refFour=$row['refFour'];
            //echo "refFour=".$refFour."#<br>";
            //echo "search:#".array_search($refFour,$listRefFour)."#<br>";
            $eanRow=array_search($refFour,$listRefFour);
        } 
    }
    if ($eanRow!=""){
        //echo "<br>eanRow:$eanRow col=".$colNb['colis']."#".($colNb['colis']+1)."#".$eanRow."#".$row['quantite']/$row['conditionnement']."<br>";
        $sheet->setCellValueByColumnAndRow($colNb['colis']+1, $eanRow, $row['quantite']/$row['conditionnement']);   
    }
    else{
        echo "<div class='redBackground'>Je ne trouve pas ".$row['designation']." ";
        if (isset($colLetter['ean'])){echo "ean=".$row['ean']." ";}
        if (isset($colLetter['refFour'])){echo "Référence Fournisseur=".$row['refFour']." ";}
        echo "</div>";
        //dispArray($row);
    }
}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workBook, "Xlsx");
// Save the spreadsheet

$writer->save($fullOutputFileName);
//$writer->save($fullFileCommandeFournisseur);
?>
