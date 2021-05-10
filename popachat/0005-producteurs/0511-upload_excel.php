<?php
require '../0022-vendor/autoload.php';
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
//include "../0021-functions/0410-getNewEan.php";
$disp=0;
$userId=$_SESSION['userInfo']['userId'];
$str="";
if ($try){
    $str.="Essai<br><br>";
}
else{
    $str.="Modification de la base<br>";
}
//------------------------------------------------------------------
// get File and save it
//------------------------------------------------------------------

$target_dir = "./files/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
$filename=$_FILES["fileToUpload"]["name"];
$str.=$filename."<br><br>";
if (!$try){
    
    // load into database
    $query="INSERT INTO `prod_import_files` (filename, author) VALUES ( '".$filename."', '$userId');";
    simple_query($query);
    $query="UPDATE prod_commande SET file='$filename' WHERE id='$commandeId'";
    simple_query($query);
    
}
// databaseClean
$query="DELETE from prod_commandeList WHERE commande_id=$commandeId;";
simple_query($query);
//----------------------------------------------------------------------
// Choose format and prepare files
//----------------------------------------------------------------------
$fournisseurParam=[];
$rowNb=[];
$colNb=[];

//$str.=$format;
$sql="SELECT formatXLS from prod_fournisseur WHERE id='".$order['fournisseur']."';";
$table=query_table($sql);
// Format has been computed in command...and is isn prod_import_fournisseurs
//$format=$table[1]['formatXLS'];
$sql="SELECT * from prod_import_readxls WHERE format='".$format."';";
$table=query_table($sql,0);
//displayinhtml($table,"",0);
//var_dump($table);
$keysList=[];
foreach ($table as $val){
    //print_r($val)."<br>";
    //$str.=$val['keyword'];
    $rowNb[$val['keyword']]=$val['row'];
    $colNb[$val['keyword']]=ord($val['col'])-ord('A');
    array_push($keysList,$val['keyword']);
}
//dispArray($colNb);
$prodArticleKeyList=["tva","ean","prixAchat","refFour","designation","conditionnement"];
$prodArticleKeyListThisInput=array_intersect($prodArticleKeyList,$keysList);
//var_dump($prodArticleKeyListThisInput);



//----------------------------------------------------------------------
// Prepare alias


//var_dump($refFourAliasDico);
//----------------------------------------------------------------------
// Read the xls file
//----------------------------------------------------------------------
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($target_file);
$sheet = $spreadsheet->getSheet(0); // Choose second sheet....
// display head of excell...
$str.="Le fichier $target_file a été chargé<br>";
 
// Store data from the activeSheet to the variable in the form of Array
//$data = array(1,$sheet->toArray("A1:B5",true,true,true)); 
//ini_set ("memory_limit", '512M');
$data = array(1,$sheet->toArray()); 
$sht=$data[1]; 
//var_dump($sht);
//$str.="sizeof data".sizeof($data);  
//$str.="coucou";
// Display the sheet content 
if (isset($rowNb['date_envoi'])){
    $date_envoi=$sht[$rowNb['date_envoi']][$colNb['date_envoi']];
    $str.="<br>La date d'envoi est ";//.$date_envoi;
    $date_envoi=decode_date_byName($date_envoi);
    $str.=$date_envoi."<br>";
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
$fullList=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat','prixVente','quantite','colis'];

$sortie=0;
//echo " beginning of loop";
$emptyRow=0; // count empty row to stop
for($row=$rowNb['prixAchat']-1;(($row<3000)&&(!$sortie));$row++){
    if (isset($sht[$row])){
        //var_dump($sht[$row]);
        //echo "<br>";
        if (!(($sht[$row][$colNb['ean']]=="")&&($sht[$row][$colNb['refFour']]==""))){
            //echo "<br>".$row."<br>";
            //echo "emptyRow $emptyRow";
            //dispArray($sht[$row]);
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
            //if ($row==32){
            //    dispArray($input);
            //}
            $continue=1;
            // Check if $ean exists
            if(array_key_exists('ean',$input)){
                // check if alias must be used.
                if (array_key_exists($input['ean'],$eanAliasDico)){
                    $input['ean']=$eanAliasDico[$input['ean']]; // replace by alias
                }
            }
            //check if refFour exists
            if(array_key_exists('refFour',$input)){
                // change to alias 
                if (array_key_exists($input['refFour'],$refFourAliasDico)){
                    $input['refFour']=$refFourAliasDico[$input['refFour']];// replace by alias
                    $input['ean']=$refFourDico[$input['refFour']];            // get back ean
                }
            }
            //dispArray($input);
            
            
            
            if(array_key_exists('quantite',$input)){
                if ($input['quantite']==0){
                    $continue=0;
                    
                }
            }
            if(array_key_exists('colis',$input)){ // does keyword "colis" exist?
                if ($input['colis']==0){ // has a colis been ordered?
                    $continue=0;
                    
                }
                else{
                    if(isset($input['conditionnement'])){
                        $input['quantite']=$input['colis']*$input['conditionnement'];
                    }
                }
            }
            
            if (substr($input['prixAchat'],1,1)=="$"){$input['prixAchat']=substr($input['prixAchat'],2);}
            //if ($row==32){echo "first=".substr($input['prixAchat'],0,2); dispArray($input); echo $continue."<br>"; echo "prixAchat".$input['prixAchat']."<br>";  }
            if ($input['prixAchat']==0){$continue=0;$emptyRow++;}
            //dispArray($input);
            //if ($row==32){ dispArray($input); echo $continue;   }
            
            
            if ($continue){
                $emptyRow=0; // reset $emptyRow because found 1.
                // Check if ean in alias.
                if(array_key_exists('ean',$input)){
                    if (array_key_exists($input['ean'],$eanAliasDico)){
                        $input['ean']=$eanAliasDico[$input['ean']];
                    }
                }
                // Check if refFour in alias
                if(array_key_exists('refFour',$input)){
                    if (array_key_exists($input['refFour'],$refFourAliasDico)){
                        $input['refFour']=$refFourAliasDico[$input['refFour']];
                    }
                }
                //var_dump($refFourAliasDico);
                //echo $input['refFour'];
            
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
                
                // list of all articles
                $uniqueQuery='SELECT * FROM prod_articles WHERE validated<2';
                //loop over keys candidates (search keys)
                //
                //var_dump($input);
                $table=0;
                $nb=0;
                for ($i=0;($i<sizeof($searchList)&&($nb!=2));$i++){
                    $key=$searchList[$i];
                    if (array_key_exists($key,$input)){
                        if ($input[$key]!=""){
                            $query="SELECT * FROM ($uniqueQuery) as prod_articles WHERE $key='".addslashes($input[$key])."'";
                            if ($try){
                                $str.=$query."<br>";
                            }
                            $table=query_table($query);
                            $nb=sizeof($table);
                            if ($nb>2){
                                echo $query;
                                echo "<div class='nonreconnu'>NON RECONNU trop d'articles<br>";
                                displayinhtml($table);
                                echo "</div>";
                            }
                            //echo "thekey is".$key." with $nb";
                        }
                    }
                }
                //echo "found! thekey is".$key." with $nb";
                /*if ($nb>10){
                    echo "die;";
                        die;
                }*/
                //displayinhtml($table);
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
                        //die();
                        if (sizeof($table)==1){
                            $str.=$query;
                            $str.="<div class='nonreconnu'>NON RECONNU<br>";
                            foreach($input as $key=>$val){
                                 $str.= $key.":".$val." ";
                            }  
                            $str.= "<br>";
                            $input['fournisseur']=$order['fournisseur'];
                            array_push($prodArticleKeyListThisInput,"fournisseur");
                            if (!isset($input['ean'])){
                                $input['ean']=new_valid_ean();
                                array_push($prodArticleKeyListThisInput,"ean");
                                
                            }
                            
                            
                            $query=create_INSERT('prod_articles',$input,$prodArticleKeyListThisInput);
                            
                            if (!$try){                   
                                simple_query($query);
                            } 
                            else{
                                $str.= $query;
                                
                            }
                            $str.="</div><br>";
                            
                        }
                        else{
                            $str.="TROP: J'ai plusieurs $refFour $design. Il y en a ".(sizeof($table)-1).".<br>";
                            displayinhtml($table);
                        }
                    }
                    // on reconnait l'article
                    else{
                        //echo "reconnu";
                        $input['ean']=$table[1]['ean'];
                        // change price
                        if(floor($table[1]['prixAchat']*1000)!=floor($input['prixAchat']*1000)){
                            //echo "on y va";
                            //var_dump($sht[$row]);
                            //echo "Pour l'attribut '$whereKey:$keyVal' la valeur '".$col[$key]."' est remplacé par '".$val."'<br>";
                            $str.="Le prix de ".$input['ean'].$design." a changé de ".$table[1]['prixAchat']." à ".$input['prixAchat']."<br>";
                            $filter=['prixAchat'];
                            $query=create_UPDATE("prod_articles",$input,$filter,'ean');
                            
                            if (!$try){ 
                                simple_query($query);
                            }
                            else{
                                $str.=$query."<br>";
                        }
                        }
                        
                        //------------------------------------------------------
                        // change price in prod_prices
                        $query="SELECT max(thedate) as thedate FROM prod_prices  where ean=".$input['ean']." group by ean";
                        $query="SELECT * FROM prod_prices where thedate in ($query) and ean=".$input['ean'];
                        $priceTable=query_table($query);
                        if (sizeof($priceTable)>1){
                            $price=$priceTable[1]['prixAchat'];
                            $id=$priceTable[1]['id'];
                        }
                        else{
                            $price=-1000;
                        }
                        // mise à jour des prix.
                        if(abs($price-$input['prixAchat'])<0.001){
                            //echo "on y va";
                            //var_dump($sht[$row]);
                            //echo "Pour l'attribut '$whereKey:$keyVal' la valeur '".$col[$key]."' est remplacé par '".$val."'<br>";
                            $str.="Le prix de ".$input['ean'].$design." a changé de ".$prix." à ".$input['prixAchat']."<br>";
                            $priceFilter=['prixAchat','thedate','source','user'];
                            $input['author']=$_SESSION['userInfo']['userId'];
                            $input['source']=$filename;
                            $input['thedate']=date("Y-m-d");
                            $input['id']=$id;
                            //echo "ici";
                            $query=create_UPDATE("prod_prices",$input,$priceFilter,'id');
                            
                            //echo $query."<br>";
                            if (!$try){             simple_query($query);}
                            //echo "et oui";
                        }
                        //------------------------------------------------------
                        // change conditionnement
                        if($table[1]['conditionnement']!=$input['conditionnement']){
                            //echo "on y va";
                            //var_dump($sht[$row]);
                            //echo "Pour l'attribut '$whereKey:$keyVal' la valeur '".$col[$key]."' est remplacé par '".$val."'<br>";
                            $str.="Le conditionnement de ".$input['ean'].$design." a changé de ".$table[1]['conditionnement']." à ".$input['conditionnement']."<br>";
                            $filter=['prixAchat'];
                            $query=create_UPDATE("prod_articles",$input,$filter,'ean');
                            //echo $query."<br>";
                            if (!$try){             simple_query($query);}
                        }
                        //query to put tri...
                        $filter=['tri'];
                        $input['tri']=$row;
                        $triQuery=create_UPDATE("prod_articles",$input,$filter,'ean');
                        //echo "coucou=".$triQuery;
                        if (!$try){simple_query($triQuery);}
                        // change
                        if ($input['quantite']!=0){ // load only if quantité is not 0
                            $filter=['commande_id','ean','prixAchat','quantite'];
                            $query=create_INSERT('prod_commandeList',$input,$filter);
                            
                            //echo $input['ean'].":".$design." ".$refFour.":";
                            
                            $str.=" ajout à la commande: quantité:".$input['quantite']." à ".$input['prixAchat']."&euro;<br>";
                            //$str.=$query."<br>";
                            if (!$try){                   
                                simple_query($query);
                            }
                            else{
                                $str.=$query."<br>";
                            }
                            
                        }
                    }
                }
            }
        }
    }
    if ($emptyRow>30){$row=10000;} //stops if more then 30 empty rows
}


?>
