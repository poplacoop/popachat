<?php


function convertArrayToDictionnary($array){
    // import $array where header contains keys
    // convert into dictionnary  

    $dbImportTable=[];
    $tmpkeys=$array[0]; // retrieve keys
    //dispArray($tmpkeys);
    $keys=[];
    for($i=0;$i<100;$i++){
        array_push($keys,$tmpkeys[$i]);
        if (!(isset($tmpkeys[$i+1]))){
                $i=1000;}
        else{
            if($tmpkeys[$i+1]==""){
                $i=1000;
            }
        }
    }
    //dispArray($keys);
    
    echo "<h1>Loop 1: create array as dictionnary</h1>";
    // loop through lines
    //ini_set("memory_limit","10M");
    foreach ($array as $key=>$row){
        //print_r($row)."<br><br>";
        if ($key>0){ // skip the first line
            if ($row[0]!=""){ // make sure the first item is not empty
                $dbRow=[];
                // loops through all the columns of $colMatch
                foreach ($keys as $col=>$key){
                    $dbRow[$key]=$row[$col];
                }
                array_push($dbImportTable,$dbRow);
            }
        }
    }
    return $dbImportTable;
}




echo "Import into database from xls<br>";
$array=import_xls($target_file,$try); // get imports into array
$tableName=array_shift($array)[0];
echo "table Name:".$tableName."<br>";;

$primary=array_shift($array)[0];
echo "primary:".$primary."<br>";;

$tmpkeys=array_shift($array);
$searchKeys=[];
    for($i=0;$i<100;$i++){
        array_push($searchKeys,$tmpkeys[$i]);
        if ($tmpkeys[$i+1]==""){$i=1000;}
    }
echo "searchKeys<br>";
dispArray($searchKeys);
echo "<br>";

//------------------------------------------------------------------
// defines column to import
$dico=convertArrayToDictionnary($array);
foreach ($dico as $row){
    //dispArray($row);
}
//dispArray($dico[0]);
$filter=array_keys($dico[0]);
$compulsory=$filter;
importFromTableKeys($dico,$tableName,$try,$primary,$searchKeys,$filter);

?>
