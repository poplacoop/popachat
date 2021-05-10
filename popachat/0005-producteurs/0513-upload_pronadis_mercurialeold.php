<?php
//echo "import";
include "../0010-admin/0510-imports_functions.php";
//------------------------------------------------------------------
// treat file
require '../0022-vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
 
$disp=0;
$userId=$_SESSION['userInfo']['userId'];
if ($try){
    echo "Essai<br><br>";
}
else{
    echo "Modification de la base<br>";
}
//------------------------------------------------------------------
// get File and save it
//------------------------------------------------------------------

$target_dir = "./files/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
$filename=$_FILES["fileToUpload"]["name"];
echo $filename."<br><br>";
if (!$try){
    // load into database
    $query="INSERT INTO `prod_import_files` (filename, author) VALUES ( '".$filename."', '$userId');";
    simple_query($query);
    $query="UPDATE prod_commande SET file='$filename' WHERE id='$commandeId'";
    simple_query($query);
    
}
        
//----------------------------------------------------------------------
// check format
$fournisseurParam=[];
$rowNb=[];
$colNb=[];


$sql="SELECT * from prod_import_readxls WHERE format=8;";
$table=query_table($sql,0);
displayinhtml($table,"",0);

//var_dump($table);
$keysList=[];
foreach ($table as $val){
    //print_r($val)."<br>";
    //echo $val['keyword'];
    $rowNb[$val['keyword']]=$val['row'];
    $colNb[$val['keyword']]=$val['col'];
    mp($val['keyword']."=(".$rowNb[$val['keyword']].",".$colNb[$val['keyword']].")",$disp);
    array_push($keysList,$val['keyword']);
}

$prodArticleKeyList=["tva","ean","prixAchat","refFour","designation","conditionnement"];
$prodArticleKeyListThisInput=array_intersect($prodArticleKeyList,$keysList);
//var_dump($prodArticleKeyListThisInput);


//var_dump($colNb);
//var_dump($rowNb);
mp("liste des clés",$disp);
//var_dump($keysList);
mp("keylist",$disp);
// open file
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($target_file);
$sheet = $spreadsheet->getSheet(0); // Choose second sheet....
// display head of excel...
 
// Store data from the activeSheet to the varibale in the form of Array
$data = array(1,$sheet->toArray(null,true,true,true)); 
$sht=$data[1]; 
//var_dump($sht);
//echo "sizeof data".sizeof($data);  
//echo "coucou";
// Display the sheet content 

$searchList=['ean','refFour','designation']; // list of key to explore.
$fullList=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat','prixVente','quantite','colis'];

// get Commande id
if ($commandeId!=""){
    $commandeQuery="SELECT A.*,B.refFour,B.conditionnement FROM (SELECT * FROM prod_commandeList WHERE commande_Id='$commandeId') as A 
    LEFT OUTER JOIN (SELECT * FROM prod_articles where validated<2) as B on A.ean=B.ean ";
    $commandeTable=query_table($commandeQuery);
    $commandeDico=create_product_dictionnary($commandeTable,"refFour");
}

// put quantité in right place
$sortie=0;
for($row=$rowNb['prixAchat'];(($row<2000)&&(!$sortie));$row++){
    //mp("row=".$row,1);
    $input=[];
    $input['commande_id']=$commandeId;
    foreach($fullList as $key){
        //mp($key,$disp);
        if(in_array($key,$keysList)){ // get value if key exists.
            //mp("load in input$key",$disp);
            if (array_key_exists($row,$sht)){
                $input[$key]=$sht[$row][$colNb[$key]];
            }
            else{
                $sortie=1;
            }
        }
    }
    // fill excel
    //var_dump($commandeDico);
    echo $input['refFour']."<br>";
    if(array_key_exists($input['refFour'],$commandeDico)){
        echo "WRITE";
        echo $row;
        echo $colNb['colis'];
        $sheet->setCellValue($colNb['colis'].$row,$commandeDico[$input['refFour']]['quantite']/$commandeDico[$input['refFour']]['conditionnement']);
    }
    
    
    $continue=1;
    /*if(array_key_exists('quantite',$input)){
        if ($input['quantite']==0){
            $continue=0;
        }
    }
    if(array_key_exists('colis',$input)){
        if ($input['colis']==0){
            $continue=0;
        }
        else{
            $input['quantite']=$input['colis']*$input['conditionnement'];
        }
    }*/
    

    if ($continue){
        mp("input=",$disp);
        //var_dump($input);
        mp("input end",$disp);
        //$input['quantite']=$sht[$rowNb['quantite']][$colNb['quantite']];
        if(array_key_exists('prixAchat',$input)){
            $prix=$input['prixAchat'];
            $newprix=explode(" ",$prix);
            //print_r($newprix);
            $prix=0;
            foreach ($newprix as $p){
                if (floatval($p)!=""){
                    $prix=floatval($p);
                }
            }
        }
        //echo $row;
        //print_r($sht[$row]);
        $input['prixAchat']=$prix;
        
        if(array_key_exists('tva',$input)){
            $tva=$input['tva'];
            if ($tva=="20%"){
                $tva=2;
            }
            else{
                $tva=1;
            }
            $input['tva']=$tva;
        }
        //mp($rowNb['prixAchat'],$disp);
        //mp($colNb['prixAchat'],$disp);
        //mp($prix,$disp);
        //$input['prixAchat']=$prix;
        //$input['prixAchat']=substr($prix,0,strlen($prix)-4);
        //echo "X".$input['prixAchat']."X";
        
        // list of all articles
        $uniqueQuery='SELECT * FROM prod_articles WHERE validated<2 and ean NOT IN (SELECT eanAlias as ean FROM prod_ean_alias)';
        //loop over keys candidates (search keys)
        //
        //var_dump($input);
        mp("input",$disp);
        $table=0;
        $nb=0;
        for ($i=0;($i<sizeof($searchList)&&($nb!=2));$i++){
            $key=$searchList[$i];
            mp($key,$disp);
            if (array_key_exists($key,$input)){
                if ($input[$key]!=""){
                    mp("H".$key."H key exists",$disp);
                    $query="SELECT * FROM ($uniqueQuery) as prod_articles WHERE $key='".addslashes($input[$key])."'";
                    mp($query,$disp);
                    $table=query_table($query);
                    $nb=sizeof($table);
                    //echo "thekey is".$key." with $nb";
                }
            }
        }
        //echo "found! thekey is".$key." with $nb";
        /*if ($nb>10){
            echo "die;";
                die;
        }*/
        //displayTableInHtml($table);
        $design="";
        if (array_key_exists('designation',$input)){
            $design=$input['designation'];
        }
        $ean="";
        if (array_key_exists('ean',$input)){
            $ean=$input['ean'];
        }
        $refFour="";
        if (array_key_exists('refFour',$input)){
            $refFour=$input['refFour'];
        }
        if ($table!=0){
            if (sizeof($table)!=2){
                //var_dump($table);
                if (sizeof($table)==1){
                    echo "NON RECONNU ";
                    foreach($input as $key=>$val){
                        echo $key.":".$val." ";
                    }  
                    $input['fournisseur']=$order['fournisseur'];
                    array_push($prodArticleKeyListThisInput,"fournisseur");
                    echo $query=create_INSERT('prod_articles',$input,$prodArticleKeyListThisInput);
                    if (!$try){                   
                        //simple_query($query);
                    } 
                    echo "<br>";
                    
                }
                else{
                    echo "TROP: J'ai plusieurs $refFour $design. Il y en a ".(sizeof($table)-1).".<br>";
                    displayTableInHtml($table);
                }
            }
            // on reconnait l'article
            else{
                $input['ean']=$table[1]['ean'];
                // change price
                if(floor($table[1]['prixAchat']*1000)!=floor($input['prixAchat']*1000)){
                    echo "Le prix de ".$input['ean'].$design." a changé de ".$table[1]['prixAchat']." à ".$input['prixAchat']."<br>";
                    $filter=['prixAchat'];
                    $query=create_UPDATE("prod_articles",$input,$filter,'ean');
                    echo $query."<br>";
                    if (!$try){             simple_query($query);}
                }
                // change price in prod_prices
                $query="SELECT max(thedate) as thedate FROM prod_prices  where ean=".$input['ean']." group by ean";
                $query="SELECT * FROM prod_prices where thedate in ($query) and ean=".$input['ean'];
                $priceTable=query_table($query);
                if (sizeof($priceTable)>1){
                    $price=$priceTable[1]['prixAchat'];
                }
                else{
                    $price=-1000;
                }
                if(abs($price-$input['prixAchat'])<0.001){
                    echo "Le prix de ".$input['ean'].$design." a changé de ".$prix." à ".$input['prixAchat']."<br>";
                    $priceFilter=['prixAchat','thedate','source','user'];
                    $input['user']=$_SESSION['userInfo']['userId'];
                    $input['source']=$filename;
                    $input['thedate']=date("Y-m-d");
                    $query=create_UPDATE("prod_articles",$input,$filter,'id');
                    echo $query."<br>";
                    if (!$try){             simple_query($query);}
                }
                    
                // change conditionnement
                if($table[1]['conditionnement']!=$input['conditionnement']){

                    echo "Le condtionnement de ".$input['ean'].$design." a changé de ".$table[1]['conditionnement']." à ".$input['conditionnement']."<br>";
                    $filter=['prixAchat'];
                    $query=create_UPDATE("prod_articles",$input,$filter,'ean');
                    echo $query."<br>";
                    if (!$try){             simple_query($query);}
                }
                //
                // modify Value in Excel

                }
            }
        }
}
$writers = new Xls($spreadsheet);
$fileName = "./files/new".$filename;
echo $fileName;
$writers->save($fileName);

?>
