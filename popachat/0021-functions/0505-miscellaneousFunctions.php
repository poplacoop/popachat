<?php 
// take care of root path
if(($_SERVER['HTTP_HOST']=="popbis.marly.ml")||($_SERVER['HTTP_HOST']=="popachat.ml")||( $_SERVER['HTTP_HOST']=="mypop.marly.ml")){
    $path="";
}
else{
    $path="/siteMypop";
}

define("dayoftheweek",['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche']);
define("rootPath",$_SERVER['DOCUMENT_ROOT'].$path);
$_SESSION['rootPath']=rootPath;

function createInputLine($id,$title,$type="",$defaultValue="",$addstr=""){
    // create a ligne in table
    $typeStr="";
    if ($type!=""){
        $typeStr=" type=$type ";        
    }
    $defaultValueStr="";
    if ($defaultValue!=""){
        $defaultValueStr=" value=$defaultValue ";        
    }
    return "<tr><td>$title</td><td><input id=$id name=$id $typeStr $defaultValueStr ></input></td></tr>";
}

function createAndEchoInputLine($id,$title,$type="",$defaultValue=""){
    echo createInputLine($id,$title,$type,$defaultValue);    
}

function splitInputHour($datetime){
    $theDate=substr($datetime,0,10);
    $theTime=substr($datetime,11,8);
    echo "Time=".$theTime;
    echo "<td>
        <input name='date' type='date' value=$theDate ></input>
        <input name='time' type='time' value=$theTime ></input>
        </td>";
}


function generateVariablesFromRequest($variables){
    foreach ($variables as $id){
        echo "\$$id=\"\";<br/>";
        echo "if (isset(\$_REQUEST['$id'])){
            \$$id=\$_REQUEST['$id'];
        };<br>";
    }
}


//----------------------------------------------------------------------
function createProduitsList($listeProduitsTable,$selected){
    $str= "<table 'theList'>";
    $str.= "<tr><th>EAN</th><th>Désignation</th><th>Prix</th>";
    for ($k=0;$k<5;$k++){
        $str.= "<th>".($k+1)."</th>";
    }
    $str.= "</tr>";
    for($i=1;$i<sizeof($listeProduitsTable);$i++){
        $row=$listeProduitsTable[$i];
        $str.= "<tr><td>".$row['ean']."</td>";
        $str.= "<td>".$row['designation']."</td>";  
        $str.= "<td>".$row['prixVente']."</td>";
        for ($k=0;$k<5;$k++){
            $checked="";
            if (isset($selected[$k])){
                if ($selected[$k]==$row['ean']){$checked='checked';}
            }
            $str.= "<td><input type=radio value=".$row['ean']."  $checked name='sel[".$k."]' value=".$row['ean'].">  </input></td>";     
        }
        $str.= "</tr>\n";
    }
    $str.= "</table>\n";
    return $str;
}
//----------------------------------------------------------------------
function createProduitsListFullk($tbl,$marque,&$totalOneByOne,$param,$radioSelect=0){
    //-----------------------------------------------------------------------
    // $param is 0 (locked) ou 1 for commande, 2 for liste below commande, 3 for liste articles.
    
    //$filter=['cumulsum','del','editean','select','editid','selectean','selectid'];
    //echo sizeof($tbl);
    //include "alias.php";
    //echo sizeof($tbl);
    //$attributeList=['ean','refFour','depar','tva','famille','designation','condi','conte','unité','p achat'];//,'p vente','stock','stock','fournisseur'];
    $commandeListe=['ean','refFour','designation','conditionnement','quantite','prixAchat',];
    $commandeFullListe=['ean','refFour','designation','conditionnement','prixAchat'];
    $editArticleListe=['ean','refFour','designation','fournisseur','departement','famille','conditionnement','unite','contenance','tva','prixAchat','prixVente'];
    if ($param<=1){ // commande
        $liste=$commandeListe;
        $primary="id";
        $class='theCommand';
    }
    if ($param==2){ // below for commande
        $liste=$commandeFullListe;
        $primary="ean";
        $class="theList";
    }
    if ($param==3){ // full list
        $liste=$editArticleListe;
        $primary="ean";
        $class="editList";
    }

    // headers
    $headstr= "<table class='$class'>\n";

    $headstr.= "
    <tr>";
    foreach($liste as $val){
            $headstr.="<th>".$val."</th>";
    }
    
    if ($param<2){
        $headstr.= "<td></td><td>cumul</td>";//echo "coucou";
    }
    
    // start loop
    $str="";
    $totalOneByOne=0;
    for($i=1;$i<sizeof($tbl);$i++){
        $row=$tbl[$i];
        
        $str.= "<tr>";
        //var_dump($filter);
        foreach($liste as $key){
            $str.="<td class='".$key."' >".$row[$key]."</td>";
        }
                
        //$str.= "<td>".$row['prixAchat']."</td>";
        if (in_array('prixVente',$liste)){
            $prixVente=number_format($row['prixAchat']*(($row['tva']==1)?1.055:1.2)/(1-$marque),2);
            $str.="<td>$prixVente</td>";
        }
        if ($param<2){
            $str.="<td >".number_format($row['quantite']*$row['prixAchat'],2)."</td>";
            $totalOneByOne+= $row['quantite']*$row['prixAchat'];
            $str.="<td>".number_format($totalOneByOne,2)."</td>";
        }
        
        if ($param==2){
            if (($radioSelect>0)&&($radioSelect==$row[$primary])){$checked="checked";}else{$checked="";}
            $str.= "<td><input type=radio value=".$row[$primary]."   name='select' $checked onclick='submit();'>  </input></td>";     
        }
        
        if (($param==1)||($param==3)){
            $str.="<td><img src='images/redCross.png' class='imgIcon' myid='".$row[$primary]."'></img></td>"; 
        }
        if (($param==1)||($param==3)){
            $str.="<td><img src='images/pencil1600.png' class='imgEdit' myid='".$row[$primary]."'></img></td>";  
        }
        
        $str.= "</tr>\n";
    }
    $str.= "</table>\n";
    return $headstr.$str;
}

//----------------------------------------------------------------------
//----------------------------------------------------------------------
function export($tbl){
    $marque=$_SESSION['marque'];
    //echo sizeof($tbl);
    //include "alias.php";
    //echo sizeof($tbl);
    $attributeList=['ean','refFour','depar','tva','famille','designation','condi','conte','unité','p achat','p vente','stock','stock','fournisseur',15,16,17,18,19,20,21,22,'kg'];
    
    
    $priceFile = fopen("./files/price.csv", "w");
    $stockFile = fopen("./files/stock.csv", "w");
    
    $priceStr="";
    $stockStr="";
    $stockStr="CodeBarre;Quantité;RefFournisseur;\n";
    fwrite($stockFile,$stockStr);
    

    foreach($attributeList as $attr){
        $priceStr.= "$attr;";
    }
    
    $priceStr.= "\n";
    fwrite($priceFile,$priceStr);
    
    for($i=1;$i<sizeof($tbl);$i++){
        $row=$tbl[$i];
        $priceStr="";
        $priceStr.=$row['ean'].";";
        $priceStr.=$row['refFour'].";";  
        $priceStr.=$row['departement'].";";
        $priceStr.=$row['tva'].";";
        $priceStr.=$row['famille'].";";
        $priceStr.=$row['designation'].";"; 
        $priceStr.=$row['conditionnement'].";";         
        $priceStr.=$row['contenance'].";";
        $priceStr.=$row['unite'].";";
        $priceStr.=$row['prixAchat'].";";
        $prixVente=$row['prixAchat']*(($row['tva']==1)?1.055:1.2)/(1-$marque);
        $prixVente=floor($prixVente*100)/100;
        $priceStr.=$prixVente.";";
        $priceStr.= ";";

        $priceStr.= ";".$row['fournisseur']."; ; ; ; ; ; ; ; ;";
        if ($row['unite']=="kg"){$priceStr.="1;";}else{$priceStr.=0;}
        $priceStr.="\n";
        fwrite($priceFile,$priceStr);
        // Warning have to put colis:
        $colis=$row['quantite']/$row['conditionnement'];;
        $stockStr=$row['ean'].";".$colis.";".$row['refFour'].";\n";
        fwrite($stockFile,$stockStr);
    }
    fclose($priceFile);
    fclose($stockFile);
    
        
}

//----------------------------------------------------------------------
function cell($row,$column){
    return chr(ord('A')+$column).$row;   
    
}
//----------------------------------------------------------------------
//   Export EXCEL
//----------------------------------------------------------------------
    require_once rootPath.'/0022-vendor/autoload.php';
  
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;
// treat EAN 000000
include "0410-getNewEan.php";

function exportPrixStockXLS($tbl){
    //echo sizeof($tbl);
    //include "alias.php";
    //echo sizeof($tbl);
    $attributeList=['ean','controleEan','refFour','designation','departement','famille','conditionnement','contenance','unité',
    'p achat','p vente','tva','stock','fournisseur',
    'sous famille','rayon','sous rayon','marque','auteur','editeur','groupe','sous-groupe','article pesé'];
    //displayinhtml($tbl);
    // get marque
    $marqueQuery="SELECT id,marque from prod_departement";
    $tableMarque=query_table($marqueQuery);
    $dicoMarque=create_one_field_dictionnary($tableMarque,"id","marque");
    //var_dump($dicoMarque);

    
    // Prepare Document
    //include './PhpSpreadsheet/Writer/Xls/Workbook.php';

    $priceWbook = new Spreadsheet();
    $priceSheet = $priceWbook->getActiveSheet(); 
    
    $priceOnlyWbook = new Spreadsheet();
    $priceOnlySheet = $priceOnlyWbook->getActiveSheet(); 
    
    $stockWbook = new Spreadsheet();
    $colisSheet = $stockWbook->getActiveSheet(); 
    
    foreach ($attributeList as $key=>$attr){
        $col=chr($key+ord('A'));
        //echo $col;
        
        $priceSheet->setCellValue($col.'1', $attr); 
         
    }
    $priceSheet->setCellValue('A1',"CodeBarre");
    $priceSheet->setCellValue('B1',"Quantité");
    $priceSheet->setCellValue('C1',"RefFournisseur;");
        
    $priceOnlySheet->setCellValue('A1',"EAN");
    $priceOnlySheet->setCellValue('B1',"PRICE;");
    
    // loop over all lines
    for($i=1;$i<sizeof($tbl);$i++){
        $row=$tbl[$i];
        $col=0;
        $ean=$row['ean'];
        if (substr($ean,7)=="0000000"){
            $eanNb=substr($ean,0,12);
            $row['ean']=$eanNb.find_last_value($eanNb);
        }
        // if sold by kg put conditionnement at 1
        if ($row['uniteVente']=="Kg"){
            $row['conditionnement']=1;
        }
        
        
        // Warning have to put colis:
        if ($row['conditionnement']==0){ //exception for conditionnement=0
            echo "<br>conditionnement nul pour ".$row['ean']." ".$row['designation']."<br>";
            //dispArray($row);
            echo $row['ean']." ".$row['designation']."<br>";
            $nbColis="";
        }
        else{// if sold by "Kg" put quantite and not colis
        		//if (substr($ean,7)=="0000000"){
        		//	$nbColis=$row['quantite'];
        		//}
        		//else{
                    $nbColis=$row['quantite']/$row['conditionnement'];
            //}
        }
        
        
        //echo cell($i,$col);
        $priceOnlySheet->setCellValue(cell($i+1,$col), $row['ean']);
        
        
        $priceSheet->setCellValue(cell($i+1,$col), $row['ean']);$col=$col+1;
        $col=$col+1; // ajouté pour le format 2021
        $priceSheet->setCellValue(cell($i+1,$col), $row['refFour']);$col=$col+1;
        $priceSheet->setCellValue(cell($i+1,$col), $row['designation']);$col=$col+1;
        $priceSheet->setCellValue(cell($i+1,$col), $row['departement']);$col=$col+1;
        $priceSheet->setCellValue(cell($i+1,$col), $row['famille']);$col=$col+1;        
        $priceSheet->setCellValue(cell($i+1,$col), $row['conditionnement']);$col=$col+1;
        $priceSheet->setCellValue(cell($i+1,$col), $row['contenance']);$col=$col+1;
        $priceSheet->setCellValue(cell($i+1,$col), $row['uniteContenance']);$col=$col+1;
        $priceSheet->setCellValue(cell($i+1,$col), number_format($row['prixAchat'],2));$col=$col+1;
        // prix de vente
        if (!isset($dicoMarque[$row['departement']])){echo "<div>problème avec ean=".$row['ean']." departement=".$row['departement']."<br>";}else{
            $prixVente=$row['prixAchat']*(($row['tva']==1)?1.055:1.2)/(1-$dicoMarque[$row['departement']]/100);
            $prixVente=floor($prixVente*100)/100;
            //echo "prixVente".$prixVente;
            $priceSheet->setCellValue(cell($i+1,$col), $prixVente);$col=$col+1;
        }
        $priceSheet->setCellValue(cell($i+1,$col), $row['tva']);$col=$col+1;
        $col=$col+1; // code stock
        $priceSheet->setCellValue(cell($i+1,$col), $row['fournisseur']);$col=$col+1;
        $col=$col+6;
        $priceSheet->setCellValue(cell($i+1,$col), $row['groupe']);$col=$col+1;
        $col=$col+1;
        if ($row['uniteVente']=="Kg"){$tag=1;}else{$tag=0;}
        // unite de vente
        $priceSheet->setCellValue(cell($i+1,$col), $tag);
        // price sheet
        $priceOnlySheet->setCellValue(cell($i+1,1), $row['prixAchat']);
        // colis sheet
        $colisSheet->setCellValue('A'.($i),$row['ean']);
        $colisSheet->setCellValue('B'.($i),$nbColis);
        $colisSheet->setCellValue('C'.($i),$row['refFour']);
    }       
    $writers = new Xls($stockWbook);
    $stockFilename = "./files/colisLivraison.xls";
    $writers->save($stockFilename);
    
    
     
    $writer = new Xls($priceWbook);   
    $priceFilename = "./files/price.xls";
    $writer->save($priceFilename);
    
    $writer = new Xls($priceOnlyWbook);   
    $priceOnlyFilename = "./files/priceSeuls.xls";
    $writer->save($priceOnlyFilename);
        
}

//--------------------------------------------------------------------
function select($table,$selected){
    $str="";
        for ($i=1;$i<sizeof($table);$i++){
            $row=$table[$i];
        //var_dump($row);
        if ($row["id"]==$selected){$checked="selected";}else{$checked="";}
        //$checked="tot";
        //echo $checked;
        $str.= "<option value='".$row['id']."' $checked >".$row['titre']."  </option>\n";
    }
    return $str;
}

function strip_spaces($val){
    while (substr($val,0,1)==" "){$val=substr($val,1);};
    while (substr($val,strlen($val)-1)==" "){
    $val=substr($val,0,strlen($val)-1);};
    return $val;
}

function convert_date($thedate,$decode=0,$shortNb=0,$skipYear=0){
    // converts a date given as YYYY-mm-dd for pdf $decode=1
    //echo "<br>date=".$thedate."<br>";
    $mois=['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $jour=['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
    $days=['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $year=substr($thedate,0,4);
    $month=substr($thedate,5,2);
    $day=substr($thedate,8,2);
    //echo "day1=".$day."end";
    $unixTimestamp = strtotime($thedate);
    
    $dayOfWeek = date("l", $unixTimestamp);
    //echo "the day2=X".$day."X";
    $dayOfWeek=($jour[array_search($dayOfWeek,$days)]);
    //echo "the day3=".$day."X<br>";
    
    if ($month==-1){
        $month=1;
    }
    //echo "month$month";
    $month=$mois[floor($month)-1];

    if ($shortNb){
        $dayOfWeek=substr($dayOfWeek,0,$shortNb);
        //echo "day=$dayOfWeek";
        $month=substr($month,0,$shortNb+1);
        $year=substr($year,0,$shortNb);
    }
    if ($skipYear){
        $yearSpace="";
    }
    else{
        $yearSpace=" ".$year;
    }
    
    if ($decode){
        //echo "décode";
        return ucfirst(utf8_decode($dayOfWeek))." ".$day." ".(ucfirst(utf8_decode($month)))." ".$year;
    }
    else{
        //echo "pas décode";
        return ucfirst($dayOfWeek)." ".$day." ".ucfirst($month).$yearSpace;
    }
    
}

function mp($str,$disp=1,$ln=1){
    
    if ($disp){
        if ($ln){
            echo $str."<br>";
        }
        else{
            echo $str;
        }
    }
}

function decode_date($thedate,$type="dm",$strPos,$del=" "){
    $monthMatch=['JANVIER'=>'01','FEVRIER'=>'02','MARS'=>'03','janvier'=>'01','fevrier'=>'02','Fevrier'=>'02'];
    $yearMatch=['2020','2021','2022','2023','2024','2025'];
    if (substr($type,0,2)=="dm"){
        mp(" ");
        mp($thedate);
        
        mp(strlen($thedate));
        $beg=strpos($thedate," ",strlen($strPos)-1);
        $end=strpos($thedate," ",$beg+1);
        mp("begDay".$beg."-".$end);
        
        $day=substr($thedate,$beg+1,$end-$beg-1);
        mp(strlen($day));
        if (strlen($day)==1){$day="0".$day;}
        mp("theDay=".$day."X");
        $beg=$end+1;
        mp("beg=".$beg);
        $end=strpos($thedate," ",$beg);
        mp("end=".$end);    
        $monthStr=substr($thedate,$beg,$end-$beg);
        $month=$monthMatch[$monthStr];
        mp($month);
        
        
        if ($type=="dm"){
            $beg=$end;
            $year="2021";
        }
        else{
            $year=substr($thedate,$end+1,4);
        }
    }
    
    mp($thedate."=>".$year."-".$month."-".$day);
    return $year."-".$month."-".$day;
    
}

function decode_date_byName($thedate){

    $monthMatch=['JANVIER'=>'01','FEVRIER'=>'02','MARS'=>'03','AVRIL'=>'04','MAI'=>'05','JUIN'=>'06','JUILLET'=>'07','AOUT'=>'08','SEPTEMBRE'=>'09','OCTOBRE'=>'10','NOVEMBRE'=>'11','DECEMBRE'=>'12','janvier'=>'01','fevrier'=>'02','Fevrier'=>'02','Février'=>'02'];
    $yearMatch=['2020','2021','2022','2023','2024','2025'];
    if($thedate==""){
        return 0;
    }
    else{
        //echo "X".$thedate."X";
        foreach ($yearMatch as $y){
            $thePos=strpos($thedate,$y);
            if ($thePos){
                $year=$y;
            }
        }
        $year="2021";

        foreach ($monthMatch as $key=>$m){
            $thePos=strpos($thedate,$key);
            
            if($thePos){
                //echo "found $key";
                
                $month=$m;
                $day=substr($thedate,$thePos-3,3);
                //echo "day=".$day."H";
                $b=substr($day,0,1);
                //echo "b=".$b."X";
                if ((ord($b)<ord('0'))||(ord($b)>ord('9'))){
                    $day=substr($day,1,2);
                    //echo "cutday1=".$day."H";
                }
                
                $e=substr($day,strlen($day)-1,1);
                //echo "e=".$e."X";
                if ((ord($e)<ord('0'))||(ord($e)>ord('9'))){
                    $day=substr($day,0,strlen($day)-1);
                    //echo "cutday2=".$day."H";
                }
                
                $b=substr($day,0,1);
                //echo "b=".$b."X";
                if ((ord($b)<ord('0'))||(ord($b)>ord('9'))){
                    $day=substr($day,1,strlen($day)-1);
                    //echo "cutday3=".$day."H";
                }
                
                $e=substr($day,strlen($day)-1,1);
                //echo "e=".$e."X";
                if ((ord($e)<ord('0'))||(ord($e)>ord('9'))){
                    $day=substr($day,0,strlen($day)-1);
                    //echo "cutday4=".$day."H";
                }
                
                //echo "X".$day."X";
                if (strlen($day)==1){
                    $day="0".$day;
                }
                $day=strval($day);
                 
            }
        }
        //$year="2021";
        if (!isset($month)){
            echo $thedate;
        }
        return $year."-".$month."-".$day;
    }
    
}


function dispArrayKeys($table){
    foreach ($table as $key=>$val){
        echo $key." | ";
    }
    echo "<br>";
}
function dispArrayVals($table){
    foreach ($table as $key=>$val){
        echo $val." | ";
    }
    echo "<br>";
}
function dispArray($table){
    foreach ($table as $key=>$val){
        echo $key."=>".$val." | ";
    }
    echo "<br>";
}

function convert_number($val){
    if (is_numeric($val)){
        if (floatval($val)==intval($val)){
            $val=intval($val);
        }
        else{
            $val=mynumber_format(floatval($val),2);
        }
    }
    return $val;
}
// convert "." to ","
function mynumber_format($nb,$dec){
    //return str_replace(".",",",number_format($nb,$dec));
    return number_format($nb,$dec);
}

// extract date from filename
function extractDateYearAndExtensionFromFilename($str){
    //echo $str;
    $pos=strpos($str,"-");
    //echo "pos=".$pos."strlen=".strlen($str);
    $thedate="";
    $year="";
    if ($pos!=""){
        $thedate=substr($str,$pos-4,10);
        $year=substr($thedate,0,4);
        //$str=substr($str,$pos+1);
        //echo "<br>newstr=".$str."<br>";
    }
    else{
        $pos=0;
    }
    $pos=strpos($str,"xls",$pos);
    //echo "pos".$pos;
    $ext=substr($str,$pos);
    echo $ext."<br>";
    return [$thedate,$year,$ext];
    
}

?>
