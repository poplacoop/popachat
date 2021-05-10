<?php

//----------------------------------------------------------------------
// Import CSV to feed database
//----------------------------------------------------------------------
function import_csv($target_file,$try){
    // file is an array  with integer indices
    $csv = $target_file;
    $csv = read($csv);
    //print_r($csv);
    
    echo "touto<br>";
    $tableName=array_shift($csv)[0];
    $primary=array_shift($csv);    
    $searchKeys=array_shift($csv);

    importFromTable($csv,$tableName,$try,$searchKeys,$primary);
}
require '../vendor/autoload.php';
 
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\xls;
    use PhpOffice\PhpSpreadsheet\IOFactory;

//----------------------------------------------------------------------
// Import XLS to table
//----------------------------------------------------------------------
function import_xls($target_file,$try,$ext){

    $spreadsheet = new Spreadsheet();
    echo $target_file;
    echo "<br>";
    //$inputFileType = 'xls';
    $inputFileName = $target_file;
    
    /**  Create a new Reader of the type defined in $inputFileType  **/
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    /**  Advise the Reader that we only want to load cell data  **/
    //$reader->setReadDataOnly(true);
    //echo "ext is $ext";
    if (!isset($ext)){
        [$thedate,$year,$ext]=extractDateYearAndExtensionFromFilename($target_file);
    }
    echo "the file extension id $ext";
    if ($ext=="xls"){
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    }
    else{
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    }
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($target_file);
    return $spreadsheet->getSheet(0)->toArray();
   
}
//----------------------------------------------------------------------
// Import table into Database
//----------------------------------------------------------------------
    
function importFromTable($table,$tableName,$try,$searchKeys,$primary,$generalFilter=[]){
    // import from table where head (first line) give attributes and column values.
    // if line exists, update the database, if not insert into the database.
    // $seachkeys=['ean','refFour'] are the keys to base identity on 
    // $tableName is the name of the table "prod_plu"
    // $primary is the "primary field" to identify line.
    // if $try is 1 then no update is done
    // $table starts at line 0 (where the headers are)
    // $generalFilter is an array with the keys required for the database
    
    if ($try){echo "<h1>Essai Seulement</h1>";}

    $headers=$table[0]; // the headers (first row)
    echo "les Entêtes<br>";
    dispArray($headers);
    if ($generalFilter==[]){
            $generalFilter=$headers;
    }
    //echo "le Filtre<br>";
    //dispArray($generalFilter);
    //echo "<br>";

    $primaryName=$primary; // primary key
    echo "la clé primaire est $primary<br>";
    
    //echo "<br>Primary<br>";
    //echo "primary name=".$primaryName;
    //echo "<br>Search keys<br>";
    //echo var_dump($searchKeys)."<br>";
    //mp("next",1);
    // Run through each line
    //echo "size of ".sizeof($table)."<br>";
    $searchIndex=[];
    foreach ($searchKeys as $keys){
        //echo "key is".$keys;
        foreach($headers as $idx=>$val){
            //echo $idx.":".$keys."=".$val."=<br>";
            if ($keys==$val){
                //echo "found $val $idx";
                array_push($searchIndex,$idx);
            }
        }
    }
    //echo "<br> les indices<br>";
    //dispArray($searchIndex); // fournd position of search
    
    echo "<h2>LOOP</h2>";
    for ($i=1;$i<sizeof($table)-1;$i++){
        echo "row".$i."-";
        $insert=[]; // where values will be stored
        //--------------------------------------
        // Prepare dictionnary for update or create
        // $input holds attributes values and $filter attributes list.
        $input=[];
        
        $line=$table[$i];  // readline
        //dispArray($line);
        // create a dictionnary for input
        for ($k=0;$k<sizeof($line);$k++){
            //echo $line[$k]."=".$headers[$k]."<br>";
            //dispArray($filter);
            //echo "X".in_array($headers[$k],$filter)."X";
            if(($line[$k]!="")&&(in_array($headers[$k],$generalFilter))){
                //echo "accepted<br>";
                $input[$headers[$k]]=$line[$k];
            }
        }
        //echo "<br>input<br>";
        //dispArray($input);
        //echo "<br>";
        //var_dump($filter);
        //--------------------------------------
        
        
        //echo "<br>";
        //print_r($tableName);
        //echo "keyval=$keyVal X";
        
        // search for keys
        $where="WHERE 1 ";
        foreach ($searchIndex as $key=>$idx){
            //echo $key;
            //print_r($line);
            $where.=" AND ".$searchKeys[$idx]."='".$line[$idx]."'";
        }
        $query="SELECT * FROM $tableName $where";
        //echo $query;
        //$query="SELECT * FROM $tableName WHERE ".$headers[0]."='".$keyVal."'";
        //if ($nbKey==2){
         //   $keyVal=$line[1];
         //   $query="SELECT * FROM ($query) as A WHERE ".$headers[1]."='".$keyVal."'";
        //}
        //echo $query."<br>";
        //echo "headers".$headers;
        $resultTable=query_table($query);
        //print_r($table);
        //echo "size=".sizeof($resultTable)."<br>";
        
        
         
        //displayTableInHtml($resultTable); 
        //echo "size=".sizeof($table);
        // case where the id does not exist
        
        $query="";
        //echo sizeof($resultTable);
        if (sizeof($resultTable)==1){
            
            $filter=$generalFilter; //filter is set to maximum
            if (sizeof($input)>0){
                echo "La ligne avec les informations va être créée dans '$tableName'<br>";
                
                //foreach ($input as $key=>$val){
                //    echo " ".$key."=".$val." ";
                //}
                //echo "<br/>";
                if (in_array($primary,$headers)){
                    array_push($filter,$primaryName); // add primary if in database
                }
                //echo "does not exist<br>";
                //echo "size=".sizeof($input);
                echo "filter".var_dump($filter);
                $query=create_INSERT($tableName,$input,$filter); 
                //echo $query."<br>";
            }
            else{
                echo "input est vide";
                dispArray($line);
            }
        }
        else{
            //echo " exists  ";
            $filter=[]; //filter is empty and get filled if value are differents
            $col=$resultTable[1];
            if ($i==-1){
                dispArray($col);
                dispArray($input);
            }
            //var_dump($col);
            //var_dump($input);
            
            foreach ($input as $key=>$val){  // loop through key of input to check match in database
                if ($i==-11){
                    echo "<br>".$key."=>".$val;
                }
                //echo " ".$col[$key];
                //echo "égalité?".(stripslashes($val))."=".stripslashes($col[$key]);
                //echo "col=".$key;
                
                $add=1;
                if(stripslashes($val)==stripslashes($col[$key])){
                    $add=0;
                    //echo "same";
                }
                //echo $key."=add1 to=".$add."<br>";
                if (floatval(stripslashes($val)!=0)){
                    if ((abs(floatval(stripslashes($val)))-abs(floatval(stripslashes($col[$key]))))<0.001*abs(floatval(stripslashes($val)))){
                             $add=0;
                    }
                }
                //echo "ooooooooooooooooooooo".$key."=add2 to=".$add."<br>";
                if ($add){
                    echo "Pour ".substr($where,12,strlen($where))." la valeur de '$key' qui était '".$col[$key]."' est remplacé par '".$val."'<br>";
                    array_push($filter,$key);
                    //echo "<br>Filter running";
                    //var_dump($filter);
                    //echo "<br>";
                    //var_dump($filter);
                }
                else{
                    //echo "égalité";
                }
            }
            //echo "filter ready<br>";
            //var_dump($filter);
            $input[$primaryName]=$col[$primaryName];
            if (sizeof($filter)>0){
                $query=create_UPDATE($tableName,$input,$filter,$primaryName);
            }
            else {
                 $query="";
                 //echo " deja dans la base ";
            }
            //echo $query."<br>";
        }
        //echo "goon<br>$query<br/>";   
        //echo "<br>".$query."<br>";
        if ((!$try)&&($query!="")){
            echo $query."<br>";
            simple_query($query)."<br>";
        }

    }
    //echo "coucou";
}


//-----------------------------------
function array_to_xls($data,$sheet){
    //set value row
    for($i=0;$i<sizeof($data);$i++){
        //set value for indi cell
        $row=$data[$i];
        //writing cell index start at 1 not 0
        $j=1;
        foreach($row as $x => $x_value) {
            $sheet->setCellValueByColumnAndRow($j,$i+1,$x_value);
            $j=$j+1;
        }
    }
return $sheet;
}


?>

