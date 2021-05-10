<?php
@session_start();
include "0002-introAdmin.php";
echo "<body>
    <div class='topBanner'>";
echo menuAdmin($menuFilter);
echo "</div>";

include "0003-prepareAdminData.php";

require '../0022-vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;    
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;      


include "0510-imports_functions.php";
include "0511-imports_fromTableKeysFunction.php";
include "0102-import-generic.php";

// Allow to have a try or not
$try=$order['try'];
//var_dump($_REQUEST);
//echo "try=".$order['try'];
if ($order['try']==1){
    echo "Essai<br>";
}
else{
    echo "Modification de la base<br>";
}


///var_dump($_REQUEST);
//------------------------------------------------------------------
// get File and save it
//------------------------------------------------------------------
if (isset($_FILES["fileToUpload"])){
    if ($_FILES["fileToUpload"]["tmp_name"]==""){echo "Choisir un fichier.<br>";}
else{
    $filename="";
    //echo "file load";
    $target_dir = "./files/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
    $filename=$_FILES["fileToUpload"]["name"];

    if (!$try){
        // load into database
        $query="INSERT INTO `prod_import_files` (filename, author) VALUES ( '".$filename."', '".$_SESSION['userInfo']['userId']."');";
        simple_query($query);
    }


    //----------------------------------------------------------------------
    // Import CSV to feed database
    //----------------------------------------------------------------------
    if ($order['upload_csv']!=""){
        include "0512-upload_csv.php";
        upload_csv($target_file,$try);
        echo "fini";
    }
    //----------------------------------------------------------------------
    // Import XLS to feed database
    //----------------------------------------------------------------------
    if ($order['upload_xls']!=""){
        
        include "0513-upload_xls.php";
        //upload_xls($target_file,$try);
        echo "fini";
    }
    //----------------------------------------------------------------------
    // get PLU
    //echo "order".$order['upload_PLU'];
    if ($order['upload_PLU']!=""){
        include "0104-import-plu.php";
    }

    //----------------------------------------------------------------------
    // import articles
    //

    if ($order['upload_articles_liste']!=""){
        $thedate=$_REQUEST['importDate'];
        echo "Import Articles chosen<br>";
        //echo "in 500-miscellaneousFunctions.py, year is 2021<br>";
        if ($target_file!=""){
        $table=import_xls($target_file,$try);
        //------------------------------------------------------------------
        // defines column to import
        $col=[];
        $colMatch=['ean'=>'B','refFour'=>'C','designation'=>'D','tva'=>'E','departement'=>'G','famille'=>'H','fournisseur'=>'L'];
        $colMatch=array_merge($colMatch,['uniteVente'=>'O','conditionnement'=>'P','prixAchat'=>'Q','prixVente'=>'U','stock'=>']']);

        //var_dump($table);
        // create first line
        $dbImportTable=[];
        $dbImportTable[0]=[];
        //$dbImportTable[1]=[];

        foreach($colMatch as $key=>$val){
            array_push($dbImportTable[0],$key);   
        }
        //var_dump($dbImportTable,$dbImportTable[0]);
        array_push($dbImportTable,$dbImportTable[0]);
        
        echo "<h1>Loop 1: create array</h1>";
        // loop through lines
        //ini_set("memory_limit","10M");
        foreach ($table as $key=>$row){
            //print_r($row)."<br><br>";
            if ($key>0){
                if ($row[0]!=""){
                    
                    $dbRow=[];
                    // loops through all the columns
                    foreach ($colMatch as $key=>$val){
                        $col=ord($val)-ord('A');
                        //$input[$key]=$row[$col];
                        array_push($dbRow,$row[$col]);
                        }
                    }
                    //print_r($dbRow);
                    //$dbRow['marque']=(1-$dbRow['prixAchat']/$dbRow['prixVente'])*100;
                    
                    array_push($dbImportTable,$dbRow);
                    
                    //echo "<br>";
                }
            }
        }
        //echo "<br><br>";
        array_shift($dbImportTable);
        
        //var_dump($dbImportTable[1]);
        $filter=array_keys($colMatch);
        // correct departement
        $attr=["departement"=>"prod_departement","famille"=>"prod_famille","fournisseur"=>"prod_fournisseur"];
        
        
        
        echo "<h1>Loop 2: match string to code</h1>";
        foreach($attr as $key=>$val){
            $query="SELECT * FROM $val;";
            $table=query_table($query);
            $dico=create_one_field_dictionnary($table,"titre","id");
            
            //echo "<br>Dico";
            //var_dump($dico);
            for ($k=0;$k<sizeof($filter);$k++){
                //echo $k."-".$key."=".$filter[$k]."<br>";
                if ($filter[$k]==$key){
                    $pos=$k;
                    //echo "found $k<br>";
                }
            }
            
            //echo "<br>key is $key, val is $val col is $pos for $val<br>$pos<br>";
            //print_r($filter);

            echo "<h1>Loop 2: subLoop: match string to code: $key</h1>";
            //var_dump($dico);
            echo "Position: $pos<br>";
            for($i=1;$i<sizeof($dbImportTable);$i++){
                $line=$dbImportTable[$i];
                
                if (!array_key_exists($line[$pos],$dico)){
                    echo "key=".$line[$pos]."=missing in line $i<br>";
                    dispArray($line);
                    echo "<br>";
                }
                else{
                    $dbImportTable[$i][$pos]=$dico[$line[$pos]];
                }
                
                //print_r($dbImportTable[$i]);
                //echo "<br><br>";
            }
            
        }
        echo "<h1>Loop 3: import in database</h1>";
        //var_dump($dbImportTable[3]);
        //die;
        $searchKeys=['ean'];
        //----------------------------------------------------------------------
        // Put in database prod_articles
        $try=0;
        echo "<h2>Les produits</h2>";
        //dispArray($colMatch);        
        $filter=array_keys($colMatch);
        //dispArray($filter); 
        unset($filter[11]);
        //dispArrayVals($filter);
        
        importFromTable($dbImportTable,'prod_articles',$try,'ean',$searchKeys,$filter);
        //----------------------------------------------------------------------
        // Put in database prod_prices
        echo "<h2>Les stocks</h2>";
        dispArray($colMatch);        
        
        foreach ($dbImportTable as $key=>$row){
            if ($key!=0){
                $dbImportTable[$key][12]=$target_file;
                $dbImportTable[$key][13]=$_REQUEST['importDate'];
            }
        }
        $dbImportTable[0][12]="source";
        $dbImportTable[0][13]="thedate";
        
        dispArrayVals($filter);
        dispArray($dbImportTable[0]);
        dispArray($dbImportTable[2]);
        // import prices
        $filter=['ean','prixAchat','source','thedate'];
        importFromTable($dbImportTable,'prod_prices',$try,'id',$searchKeys,$filter);
        
        // import stocks
        $filter=['ean','stock','source','thedate'];
        importFromTable($dbImportTable,'prod_stock',$try,'id',$searchKeys,$filter);
        
        //var_dump($dbImportTable);
        //----------------------------------------------------------------------
        // Save
        $workBook = new Spreadsheet();
        $sheet = $workBook->getActiveSheet(); 
        array_to_xls($dbImportTable,$sheet);
        $writer = new Xls($workBook);   
            
        //$writer->save('files/liste.xls');
        }

    //----------------------------------------------------------------------
    // get Journal
    //echo "order".$order['upload_PLU'];
    if ($order['upload_journal']!=""){
        echo "Journal chosen<br>";
        
        if ($target_file!=""){
        $table=import_xls($target_file,$try);
        
        
        //var_dump($table);
        // create first line
        $dbImportTable=[];
        $headers=['thedate','amount','typepaiement','noticket','ticketNF'];
        $typesPaiments=['CARTE','BON ACHAT','CHEQUE','EN CREDIT'];
        //array_push($dbImportTable,$headers); // first line as headers
        //------------------------------------------------------------------
        echo "<h1>Loop 1: create array</h1>";
        // loop through lines
        //ini_set("memory_limit","10M");
        $init=1;
        foreach ($table as $key=>$row){  // loop through lines
            if (($row[0]!="")&&(!$init)){  // avoid empty rows
                $dbRow=[];
                //dispArray($row);
                $beg=strpos($row[1],"-");
                $end=strpos($row[1],"/",$beg+1);
                $ticketNb=substr($row[1],$beg+1,$end-$beg-4);
                while (strlen($ticketNb)<6){$ticketNb="0".$ticketNb;}
                //echo $row[6];
                if($ticketNb!=$row[6]){
                    echo 'date non reconnue<br>';
                    dispArray($row);
                }
                else{
                    $thedate=substr($row[1],$end-2,10);
                    //mp($thedate);
                    $day=substr($thedate,0,2);
                    $month=substr($thedate,3,2);
                    $year=substr($thedate,6,6);
                    $dateNew = $year."/".$month."/".$day;
                    $dbRow['thedate']=$dateNew." ".substr($row[1],$end+11,5).":00";
                    //$dbRow['hour']=$row[0];
                    $dbRow['amount']=$row[2];
                    $dbRow['ticket']=$ticketNb;
                } 
                array_push($dbImportTable,$dbRow); 
            }
        
            $init=0; //(skip first line)
        }
        //echo "<br><br>";
        dispArray($dbImportTable[0]);
        //dispArray($dbImportTable[1]);
        
        //----------------------------------------------------------------------
        // Put in database
        echo "<h1>Loop 2: import in database</h1>";
        $searchKeys=['thedate','ticket'];
        importFromTableKeys($dbImportTable,'prod_journal',$try,'id',$searchKeys,[],"thedate");
        //var_dump($dbImportTable);
        //----------------------------------------------------------------------
        // Save
        //$workBook = new Spreadsheet();
        //$sheet = $workBook->getActiveSheet(); 
        //array_to_xls($dbImportTable,$sheet);
        //$writer = new Xls($workBook);   
            
        //$writer->save('files/PLU.xls');
        }
    }

    //----------------------------------------------------------------------
    // import stock
    //
    if ($order['upload_stock']!=""){
        // defines column to import
       include "0103-import-stock.php";
        //$writer->save('files/liste.xls');
        }
    //----------------------------------------------------------------------
    // import stock movement
    //

    if ($order['upload_stock_mvt']!=""){
        include "0100-import_stock_mvt.php";

    }
    //----------------------------------------------------------------------
    // import articles 
    //

    if ($order['upload_articles']!=""){
        include "0101-import-articles.php";

    }  
    
    //----------------------------------------------------------------------
    if ($order['upload_prices']!=""){
        include "0102-import-prices.php";

    }  
    
      
    
    
}
// end of file upload
}
//----------------------------------------------------------------------
// html
//----------------------------------------------------------------------


$html= "<form id='myForm' method='post' enctype='multipart/form-data'>";

$html.= "<input type='file' name='fileToUpload' id='fileToUpload'><br><br>";
$html.= "<input type='date' name='importDate' id='importDate' value='".date("Y-m-d")."'><br><br>";
$html.= "<button type='date' name='reset' id='reset' >reset</button><br><br>";
$html.="<div class='csvbase'>";
$html.= "<table>";
$html.="<tr>
        <td></td>
        <td>Pour importer, 
        <ol>
        <li>
        première ligne nom de la table</li>
        <li>deuxième ligne nom de la clé primaire</li>
        <li>troisième ligne attributs à vérifier</li>
        <li>ensuite tous attributs fournis (nom) en ligne</li>
        <li>puis les valeurs...</li>
        </ol>
        </td></tr>";

$html.= "<tr class='csv'><td>Importer les produits</td>
    
    <td><button id='upload_csv' name='upload_csv' value=1>Importer sous csv</button></td>
    </tr>
    ";
$html.= "<tr class='xls'><td>Importer les produits</td>
    
    <td><button id='upload_xls' name='upload_xls' value=1>Importer sous xls</button></td><td><a href='./files/46-CAFES_FACTORERIE.xls'>Exemple</a></td>
    </tr>
    ";

$html.= "<tr class='plu'><td><input name='pluYear' value='2021'></input></td>
    <td><button id='upload_PLU' name='upload_PLU' value=1>Importer le PLU</button></td>";
$html.="<td><a href='./files/PLU.xls'>Le fichier PLU Excel</a></td>
    </tr>
    ";
$html.= "<tr class='plu'><td>Importer les produits</td>
    
    <td><button id='upload_articles' name='upload_articles' value=1>Importer les articles</button></td>
    </tr>
    ";
$html.= "<tr class='plu'><td>Importer le journal de caisse</td>";
$html.="    <td><button id='upload_journal' name='upload_journal' value=1>Importer le journal</button></td>
    </tr>
    ";
    
$html.= "<tr class='plu'><td>Importer les prix</td>";
$html.="    <td><button id='upload_prices' name='upload_prices' value=1>Importer les prix de cosme</button></td>
    </tr>
    ";
    
$html.= "<tr class='plu'><td>Importer le stock</td>";
$html.="    <td><button id='upload_stock' name='upload_stock' value=1>Importer le stock</button></td>
    </tr>
    ";
    
$html.= "<tr class='plu'><td>Importer les Mouvements de Stock</td>";
$html.="    <td><button id='upload_stock_mvt' name='upload_stock_mvt' value=1>Importer les mouvement de stock</button></td>
    </tr>
    ";
$html.="<td>Essai?</td><td><input type='checkbox' id='try' name='try' value=1 checked></input></td></td>";
$html.="</table>
</div>";
$html.= "</form>";
//----------------------------------------------------------------------
// Create the body of the page
//----------------------------------------------------------------------



echo $html;




?>
