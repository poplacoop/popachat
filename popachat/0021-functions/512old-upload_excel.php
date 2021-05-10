<?php
require '../0022-vendor/autoload.php';
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
$disp=0;
// check if load or only try.


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
echo $filename."<br>coucou<br>";
if (!$try){
    // load into database
    $query="INSERT INTO `prod_import_files` (filename, author) VALUES ( '".$filename."', '$userId');";
    simple_query($query);
    $query="UPDATE prod_commande SET file='$filename' WHERE id='$commandeId'";
    simple_query($query);
    
}
//----------------------------------------------------------------------
// databaseClean
// Erase the command
$query="DELETE from prod_commandeList WHERE commande_id=$commandeId;";
simple_query($query);
//----------------------------------------------------------------------
// Choose format and prepare files
//----------------------------------------------------------------------
$fournisseurParam=[];
$rowNb=[];
$colNb=[];

//echo $format;
$sql="SELECT formatXLS from prod_fournisseur WHERE id='".$order['fournisseur']."';";
$table=query_table($sql);
$format=$table[1]['formatXLS'];
$sql="SELECT * from prod_import_readxls WHERE format='".$format."';";
$table=query_table($sql,0);
displayTableInHtml($table,"",0);  // displayFormat
//var_dump($table);
//    Loop over variables to create the list of variables.

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
//----------------------------------------------------------------------
// Prepare alias



//----------------------------------------------------------------------
// Read the xls file
//----------------------------------------------------------------------
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($target_file);
$sheet = $spreadsheet->getSheet(0); // Choose second sheet....
// display head of excell...
 
// Store data from the activeSheet to the varibale in the form of Array
$data = array(1,$sheet->toArray(null,true,true,true)); 
$sht=$data[1]; 
//var_dump($sht);
//echo "sizeof data".sizeof($data);  
//echo "coucou";
// Display the sheet content 
if (isset($rowNb['date_envoi'])){
    $date_envoi=$sht[$rowNb['date_envoi']][$colNb['date_envoi']];
    echo "<br>La date d'envoi est ";//.$date_envoi;
    $date_envoi=decode_date_byName($date_envoi);
    echo $date_envoi."<br>";
    $query="UPDATE prod_commande SET date_envoi='".$date_envoi."' WHERE id='$commandeId';";
    simple_query($query);
}
if (isset($rowNb['date_livraison_prevue'])){
    if ($rowNb['date_livraison_prevue']!=""){
        $date_livraison_prevue=$sht[$rowNb['date_livraison_prevue']][$colNb['date_livraison_prevue']];
        //echo "coucou";
        //echo "coucouend";
        $date_livraison_prevue=decode_date_byName($date_livraison_prevue);
        $query="UPDATE prod_commande SET date_envoi='".$date_livraison_prevue."' WHERE id='$commandeId';";
        simple_query($query);
    }
}


$searchList=['ean','refFour','designation']; // list of key to explore.
$fullList=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat','prixVente','quantite'];
//----------------------------------------------------------------------
// Loop over the lines of the file...
$sortie=0;
for($row=$rowNb['prixAchat'];(($row<2000)&&(!$sortie));$row++){
    //mp("row=".$row,1);
    $input=[];
    $input['commande_id']=$commandeId;
    // for the keys in $fullList get the ones that exist in the file.
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
    // $continue is a variable to stop reading the line.
    $continue=1;
    if(array_key_exists('quantite',$input)){
        if ($input['quantite']==0){
            $continue=0;
        }
    }
    if ($continue){
        if(array_key_exists('ean',$input)){
            if (array_key_exists($eanAliasDico,$input['ean'])){
                $input['ean']=$eanAliasDico[$input['ean']];
            }
        }
        if(array_key_exists('refFour',$input)){
            if (array_key_exists($refFourAliasDico,$input['refFour'])){
                $input['refFour']=$eanAliasDico[$input['refFour']];
            }
        }
        
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
       
        // list of all articles except the one that are redirected
        $uniqueQuery='SELECT * FROM prod_articles and ean NOT IN (SELECT eanAlias as ean FROM prod_ean_alias)';
        //--------------------------------------------------------------
        //loop over keys candidates (search keys)
        //
        //var_dump($input);
        mp("input",$disp);
        $table=0;
        $nb=0;
        for ($i=0;($i<sizeof($searchList)&&($nb!=2));$i++){ // if found item, $nb=2 then exit
            $key=$searchList[$i];
            mp($key,$disp);
            if (array_key_exists($key,$input)){
                if ($input[$key]!=""){
                    mp("H".$key."H key exists",$disp);
                    $query="SELECT * FROM ($uniqueQuery) as prod_articles WHERE $key='".$input[$key]."'";
                    mp($query,$disp);
                    $table=query_table($query);
                    $nb=sizeof($table);
                }
            }
        }

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
                if (sizeof($table)==1){// On n'a pas reconnu le produit
                    echo "NON RECONNU ";
                    foreach($input as $key=>$val){
                        echo $key.":".$val." ";
                    }  
                    $input['fournisseur']=$order['fournisseur'];
                    array_push($prodArticleKeyListThisInput,"fournisseur");
                    echo $query=create_INSERT('prod_articles',$input,$prodArticleKeyListThisInput);
                    if (!$try){                   
                        simple_query($query);
                    } 
                    echo "<br>";
                    
                }
                else{ // J'en ai trop
                    echo "TROP: J'ai plusieurs $refFour $design. Il y en a ".(sizeof($table)-1).".<br>";
                    displayTableInHtml($table);
                }
            }
            // on reconnait l'article
            else{
                $input['ean']=$table[1]['ean'];
                // change price
                if(floor($table[1]['prixAchat']*1000)!=floor($input['prixAchat']*1000)){
                    //echo "on y va";
                    //var_dump($sht[$row]);
                    //echo "Pour l'attribut '$whereKey:$keyVal' la valeur '".$col[$key]."' est remplacé par '".$val."'<br>";
                    echo "Le prix de ".$input['ean'].$design." a changé de ".$table[1]['prixAchat']." à ".$input['prixAchat']."<br>";
                    $filter=['prixAchat'];
                    $query=create_UPDATE("prod_articles",$input,$filter,'ean');
                    echo $query."<br>";
                    
                    
                    $filter=['tri'];
                    $triQuery=create_UPDATE("prod_articles",$input,$filter,'ean');
                    if (!$try){simple_query($query);}
                }
                //query to put tri...
                $filter=['tri'];
                $input['tri']=$row;
                $triQuery=create_UPDATE("prod_articles",$input,$filter,'ean');
                echo "coucou=".$triQuery;
                if (!$try){simple_query($triQuery);}
                
                // input line in commande
                if ($input['quantite']!=0){ // load only if quantité is not 0
                    $filter=['commande_id','ean','prixAchat','quantite'];
                    $query=create_INSERT('prod_commandeList',$input,$filter);
                    if($try){
                        //echo $query."<br>";
                    }
                    echo $input['ean'].":".$design." ".$refFour.":";
                    
                    echo " ajout à la commande: quantité:".$input['quantite']." à ".$input['prixAchat']."&euro;<br>";
                    echo $query."<br>";
                    if (!$try){                   
                        simple_query($query);
                        
                    }
                }
            }
        }
    }
}


?>
