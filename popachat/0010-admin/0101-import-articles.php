<?php

$thedate=$_REQUEST['importDate'];
echo "Import Articles chosen<br>";
echo "in 500-miscellaneousFunctions.py, year is 2021<br>";
//$array=import_xlsx($target_file,$try); // get imports
$array=import_xls($target_file,$try); // get imports
[$thedate,$year,$ext]=extractDateYearAndExtensionFromFilename($target_file);



//------------------------------------------------------------------
// defines column to import
$colMatch=['ean'=>'B','refFour'=>'C','designation'=>'D','tva'=>'E',];
$colMatch=array_merge($colMatch,['conditionnement'=>'P','prixAchat'=>'T','prixVente'=>'U','stock'=>chr(ord('Z')+2)]);
$colMatch=array_merge($colMatch,['fournisseurTxt'=>'L','departementTxt'=>'G','familleTxt'=>'H','groupeTxt'=>'J']);
$dicoXls=importXlsArrayToDictionnary($array,$colMatch);

//----------------------------------------------------------------------
// Convert fields
//
// liste famile à convertir (from ....Txt to ...)
$attr=["departement","famille","fournisseur","groupe"];
// import articles
echo "<h1>Importer les articles</h1>";
$primary="ean";
$tableName="prod_articles";
$filter=['ean','refFour','designation','tva','conditionnement','prixAchat','prixVente','author','fournisseur','departement','famille','groupe'];
$searchKeys=['ean'];

// loop over lines of file
foreach ($dicoXls as $key=>$val){
    //dispArray($val);
    $dicoXls[$key]['author']=$_SESSION['userInfo']['userId'];
    $dicoXls[$key]['thedate']=$thedate;
    // convert text to code for $attr
    // loop through keyword
    foreach ($attr as $name){
        //echo $name;
        //dispArray($dico[$name."_rev"]);
        // if in dictionnary convert
        if (!isset($dico[$name."_rev"][$val[$name."Txt"]])){
            if ($val[$name."Txt"]!=""){
                echo "<span class='backgroundRed'>Problem with $name<br> can not find ".$val[$name."Txt"]."</span><br>";
                dispArray($val);
                echo "<br>";
            }
            else{// if empty put empty
                $dicoXls[$key][$name]="";
            }
        }
        else{ // if ok convert
            $dicoXls[$key][$name]=$dico[$name."_rev"][$val[$name."Txt"]];
        }
    }
    // erase line where designation is nothing
    if ($dicoXls[$key]['ean']==""){
        unset($dicoXls[$key]);
    }
    $dicoXls[$key]['source']=$target_file;
    //echo "dicoXls<br>";
    //dispArray($dicoXls[$key]);
}
//----------------------------------------------------------------------
// input into database

uploadIntoDatabase($dicoXls,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'ean');


// import prices
echo "<h1>Importer les Prix</h1>";
$filter=['ean','prixAchat','prixVente','source','thedate','author'];
$tableName='prod_prices';
importFromTableKeys($dicoXls,$tableName,$try,$primary,$searchKeys,$filter,'prixAchat');
//uploadIntoDatabase($dicoXls,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'prixAchat');


// import stocks
echo "<h1>Importer les Stock</h1>";

$filter=['ean','stock','source','thedate','author'];
$searchKeys=['ean','thedate','source'];
$tableName='prod_stock';

var_dump($dicoXls);
uploadIntoDatabase($dicoXls,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'stock');


/*
die;

    //----------------------------------------------------------------------
    // import articles
    //


        $thedate=$_REQUEST['importDate'];
        echo "Module Import Articles chosen<br>";
        //echo "in 500-miscellaneousFunctions.py, year is 2021<br>";
        if ($target_file!=""){
        $table=import_xls($target_file,$try);
        //------------------------------------------------------------------
        // defines column to import
        $col=[];
        //$colMatch=['ean'=>'B','refFour'=>'C','designation'=>'D','departement'=>'G','famille'=>'H','fournisseur'=>'L','tva'=>'E',];
        $colMatch=['ean'=>'B','refFour'=>'C','designation'=>'D','tva'=>'E',];
        $colMatch=array_merge($colMatch,['conditionnement'=>'P','prixAchat'=>'T','prixVente'=>'U']);

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
        //$attr=["departement"=>"prod_departement","famille"=>"prod_famille","fournisseur"=>"prod_fournisseur"];
        
        
       
        echo "<h1>Loop 3: import in database</h1>";
        //var_dump($dbImportTable[3]);
        //die;
        $searchKeys=['ean'];
        //----------------------------------------------------------------------
        // Put in database prod_articles
        
        echo "<h2>Les produits</h2>";
        //dispArray($colMatch);        
        $filter=array_keys($colMatch);
        dispArray($filter); 
        unset($filter[11]);
        dispArrayVals($filter);
        echo "<h1>Importer les Articles</h1>";
        importFromTable($dbImportTable,'prod_articles',$try,$searchKeys,'ean',$filter);
        
        //----------------------------------------------------------------------
        // Put in database prod_prices
        echo "<h2>Les stocks</h2>";
        dispArray($colMatch);        
        
        foreach ($dbImportTable as $key=>$row){
            if ($key!=0){
                $dbImportTable[$key][12]=$target_file;
                $dbImportTable[$key][13]=$_REQUEST['importDate'];
                $dbImportTable[$key][14]=$_SESSION['userInfo']['userId'];
            }
        }
        $dbImportTable[0][12]="source";
        $dbImportTable[0][13]="thedate";
        $dbImportTable[0][14]="user";
        
        dispArrayVals($filter);
        dispArray($dbImportTable[0]);
        dispArray($dbImportTable[2]);
       
        // import prices
        echo "<h1>Importer les Prix</h1>";
        $filter=['ean','prixAchat','source','thedate','user'];
        importFromTable($dbImportTable,'prod_prices',$try,$searchKeys,'id',$filter,"prixAchat");
        
        // import stocks
        $filter=['ean','stock','source','thedate','user'];
        echo "<h1>Importer les Stock</h1>";
        importFromTable($dbImportTable,'prod_stock',$try,$searchKeys,'id',$filter,"stock");
    */
        //var_dump($dbImportTable);
        //----------------------------------------------------------------------
        // Save
        //$workBook = new Spreadsheet();
        //$sheet = $workBook->getActiveSheet(); 
        //array_to_xls($dbImportTable,$sheet);
        //$writer = new Xls($workBook);   
            
        //$writer->save('files/liste.xls');
 
 




?>
