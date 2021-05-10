<?php
//----------------------------------------------------------------------
// Generic import from Excel
// $filename=name of the file


//----------------------------------------------------------------------
// $table is array idx=> val: 0=>"2000000...", 1=>...etc 
// $primary is reference key "ean"
// $filter=['ean','stock,'prixAchat'] values to update
// $database: destination database

// from an array return a dictionnary with keys as headers
// $keys are given in $colMatch=['ean'=>'B','refFour'=>'C','designation'=>'D',
function importXlsArrayToDictionnary($array,$colMatch){
    $dbImportTable=[];
    $keys=array_keys($array[0]); // retrieve keys
    echo "<h1>Loop 1: create array as dictionnary</h1>";
    // loop through lines
    //ini_set("memory_limit","10M");
    foreach ($array as $key=>$row){
        //print_r($row)."<br><br>";
        if ($key>0){ // skip the first line
            if ($row[0]!=""){ // make sure the first item is not empty
                $dbRow=[];
                // loops through all the columns of $colMatch
                foreach ($colMatch as $key=>$val){
                    $col=ord($val)-ord('A');
                    //$input[$key]=$row[$col];
                    //echo $col."=".$keys[$col]."<br>";
                    $dbRow[$key]=$row[$col];
                }
                array_push($dbImportTable,$dbRow);
            }
        }
    }
    return $dbImportTable;
}

function uploadIntoDatabase($dictionnary,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,$compulsory=[]){
    // upload a $dictionnary : keyword => val
    // $colMatch contains the key to upload
    // $primary  is an array which is the set of values to check ['ean','thedate']
    // $filter is the liste of key to update
    // $tableName is the name of the table
    // define the keys which are on the first line

    // gets back the keys
    $keys=[];
    foreach($colMatch as $key=>$val){
        array_push($keys,$key);   
    }

    //echo "<h1>Import in database</h1>";

    //$searchKeys=['ean'];
    //----------------------------------------------------------------------
    // Put in database prod_articles

    //echo "<h2>Les produits</h2>";
    //dispArray($colMatch);        
    //$filter=array_keys($colMatch);
    //dispArray($filter); 
    //unset($filter[11]);
    dispArrayVals($filter);
    dispArray($searchKeys);
    importFromTableKeys($dictionnary,$tableName,$try,$primary,$searchKeys,$filter,$compulsory);
}
/*
//var_dump($dbImportTable);
//----------------------------------------------------------------------
// Save
$workBook = new Spreadsheet();
$sheet = $workBook->getActiveSheet(); 
array_to_xls($dbImportTable,$sheet);
$writer = new Xls($workBook);   
    
//$writer->save('files/liste.xls');
*/



?>
