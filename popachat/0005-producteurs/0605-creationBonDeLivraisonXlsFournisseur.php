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
$format=$table[1]['formatXLS'];
$sql="SELECT * from prod_import_readxls WHERE format='".$format."';";
$table=query_table($sql,0);
displayinhtml($table,"",0);
//var_dump($table);
$keysList=[];
foreach ($table as $val){
    //print_r($val)."<br>";
    //$str.=$val['keyword'];
    $rowNb[$val['keyword']]=$val['row'];
    $colNb[$val['keyword']]=ord($val['col'])-ord('A');
    mp($val['keyword']."=(".$rowNb[$val['keyword']].",".$colNb[$val['keyword']].")",$disp);
    array_push($keysList,$val['keyword']);
}
//dispArray($colNb);
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
//$query="SELECT refFourAlias,refFour FROM prod_refFour_alias";
//$table=query_table($query,1);
//$refFourOneDico=create_product_dictionnary($table,'refFourAlias');
//var_dump($refFourOneDico);
//echo "the object".$refFourOneDico['LEG774']['refFour'];

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
for($row=$rowNb['prixAchat'];(($row<3000)&&(!$sortie));$row++){
    //echo "<br>".$row;
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
    $continue=1;
    
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
    if ($input['prixAchat']==0){$continue=0;$emptyRow++;}
    //dispArray($input);
    
    if ($continue){
        $emptyRow=0; // reset $emptyRow because found 1.
        if(array_key_exists('ean',$input)){
            if (array_key_exists($input['ean'],$eanAliasDico)){
                $input['ean']=$eanAliasDico[$input['ean']];
            }
        }
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
        $uniqueQuery='SELECT * FROM prod_articles WHERE validated<2 and ean NOT IN (SELECT eanAlias as ean FROM prod_ean_alias)';
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
                    //$str.=$query."<br>";
                    $table=query_table($query);
                    $nb=sizeof($table);
                    if ($nb>2){
                        echo "<div class='nonreconnu'>NON RECONNU<br>";
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
                    $str.= $query;
                    if (!$try){                   
                        simple_query($query);
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
                    $str.=$query."<br>";
                    if (!$try){             simple_query($query);}
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
                    if($try){
                        //echo $query."<br>";
                    }
                    //echo $input['ean'].":".$design." ".$refFour.":";
                    
                    $str.=" ajout à la commande: quantité:".$input['quantite']." à ".$input['prixAchat']."&euro;<br>";
                    //$str.=$query."<br>";
                    if (!$try){                   
                        simple_query($query);
                    }
                }
            }
        }
    
    }
    if ($emptyRow>30){$row=10000;} //stops if more then 30 empty rows
}



PREVIOUS
//////////////////////////////////////////////////
//----------------------------------------------------------------------

//var_dump($field);
    $Wbook = new Spreadsheet();
    $Sheet = $Wbook->getActiveSheet(); 
    
    // headers
    $colLeft=0;
    $colRight=3;

    $col=0;
 
    // put in bold
    $style = $Sheet->getStyle('A3:B7');
    $style->applyFromArray($boldFontstyleSet);

    $Sheet->getRowDimension(1)->setRowHeight(32); 
    $Sheet->mergeCells("A1:E1");
    $Sheet->setCellValue(cell(1,0), 'BON DE COMMANDE');

    $style = $Sheet->getStyle('A1');
    $style->applyFromArray($styleSet); 
    $style = $Sheet->getStyle('A1');
    $style->applyFromArray($borderStyleSet); 
    
    
    $row=2;
    $cell="Commande no ".$field['id'];
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell="Le ".convert_date($field['date_envoi']); // adresse fournisseur
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    //line 3 - col 1
    $row=3;
    $cell="Livraison le ".convert_date($field['date_livraison_prevue'],1);
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell=utf8_decode($field['titre']); // adresse fournisseur
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 4 - col 1
    $cell="Matin (8h30 - 12h30)";
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell=utf8_decode($field['adresse1']);
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 5
    $cell=$field['popnom'];  
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    //echo "<br>toto".utf8_decode($field['adresse2']);
    $cell=utf8_decode($field['adresse2']);
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 6
    $cell=utf8_decode($field['popadresse1']);
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell=utf8_decode($field['adresse3']);
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    //line 7
    $cell=utf8_decode($field['popadresse2']);
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    //echo "<br>telephone is ".$field['telephone'];
    $cell=$field['telephone'];
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    //echo "<br>contact is ".$field['contact'];
    //line 8
    $cell="Contact: ".$refPop;
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell="".$field['contact'];
     ///echo $cell;
    if ($cell==""){$cell="";}
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    //line 9
    $row=9;
    $cell="Contact: Hélène Quévremont";
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell="".$field['email'];
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 11
    $row=11;
    $headers=["Réf.","Libellé","Quantité","Prix unitaire","Montant"];
    $col=0;
    foreach ($headers as $cell){
        $style = $Sheet->getStyle(cell($row,$col));
        $style->applyFromArray($colorStyleSet); 
        $Sheet->setCellValue(cell($row,$col++), $cell);

    }
   
    $row++;
    $str="<table>\n";
    $str.="<tr>";

    
    // main 

    $total=0; 
    for ($k=1;$k<sizeof($commandeListTable);$k++){
        //$Sheet->setCellValue(cell($row,0), substr($commandeListTable[$k]['designation'],0,45));$row+1;
        $rowList=[$commandeListTable[$k]['refFour'],$commandeListTable[$k]['designation'],
                  $commandeListTable[$k]['quantite'],$commandeListTable[$k]['prixAchat'],
        mynumber_format($commandeListTable[$k]['quantite']*$commandeListTable[$k]['prixAchat'],2)];
        $col=0;
        foreach ($rowList as $cell){
            
            $style = $Sheet->getStyle(cell($row,$col));
            $style->applyFromArray($borderStyleSet); 
            $Sheet->setCellValue(cell($row,$col++), $cell);
            $str.="<td>".convert_number($row[$k])."</td>";
            
        }
        $row++;
        $total+=$commandeListTable[$k]['quantite']*$commandeListTable[$k]['prixAchat'];
        $str.="</tr>";
    }
    
    
    // Total line
    $cell="Montant Total";
    $Sheet->setCellValue(cell($row,$col-2), $cell);
    
    $cell=convert_number($total);
    $Sheet->setCellValue(cell($row,$col-1), $cell);
    
    $str.="<tr><td></td><td></td><td></td><td>".convert_number($total)."</td>";
    
    formatCell($Sheet,$row,$col-2,$row,$col-1,$boldFontstyleSet);
    formatCell($Sheet,$row,$col-2,$row,$col-1,$borderStyleSet);
    $style = $Sheet->getStyle('D15:E15');
    $Sheet->getRowDimension($row)->setRowHeight(18);
    //$style->applyFromArray($boldFontstyleSet);
    

    
    $str.="</table>";
  
    //$Sheet->getColumnDimensionByColumn(2)->setAutoSize(true);
    //$Sheet->getColumnDimensionByColumn(3)->setAutoSize(true);
    // set column width
    // to convert inch to number coef: x12.7
    $Sheet->getColumnDimensionByColumn(1)->setWidth('8');
    $Sheet->getColumnDimensionByColumn(2)->setWidth('37');
    $Sheet->getColumnDimensionByColumn(3)->setWidth('12');
    $Sheet->getColumnDimensionByColumn(4)->setWidth('16');
    $Sheet->getColumnDimensionByColumn(5)->setWidth('16');
  
    $style = $Sheet->getStyle('A6:B7');
    $Sheet->getRowDimension(6)->setRowHeight(20);
    $Sheet->getRowDimension(7)->setRowHeight(20);
    $Sheet->getRowDimension(4)->setRowHeight(20);
    $Sheet->getRowDimension(5)->setRowHeight(20);
    $Sheet->getRowDimension(3)->setRowHeight(20);
  
    $Sheet ->getStyle('A1:E'.($k-1))->applyFromArray($styleArray);
  
  
    
    $writers = new Xls($Wbook);
    $fileName = './files/bon-'.$outputFilename.'.xls';
    //echo $fileName;
    $writers->save($fileName);
    
    //return $str;

     
    
    
        

