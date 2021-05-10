<?php
$userId=$_SESSION['userInfo']['userId'];
// defines column to import
$col=[];
$colMatch=['thedate'=>'C','type'=>'B','bl'=>'E','ean'=>'F','oldStock'=>'I','ajout'=>'J','raison'=>'O'];
//------------
// define $colMatchNumber
$colMatchNumber=[];
foreach($colMatch as $key=>$val){
    if (strlen($val)==1){
    //echo "ord=".ord($val)."<br>";
    }
    if (strlen($val)==2){
        //echo ord(substr($val,0,1));
        //echo ord(substr($val,1,1))-ord('A')+1;
        $ord=ord(substr($val,0,1))+ord(substr($val,1,1))-ord('A')+1;
        //echo $ord;
        $colMatch[$key]=chr($ord);
        //echo chr($ord);
        //echo "<br>";
    };
    if (!array_key_exists($key,$colMatchNumber)){
        $colMatchNumber[$key]=ord($colMatch[$key])-ord('A')+1;
    }
}
echo "Lecture fichier excel<br>";
dispArray($colMatchNumber);
//-------------------

// import excel
//die;
$thedate=$_REQUEST['importDate'];
echo "Import Stock Mvt chosen<br>";
if ($target_file!=""){
    $table=import_xls($target_file,$try);
    //------------------------------------------------------------------
    //var_dump($table);
    // create first line
    $dbImportTable=[];
    //$dbImportTable[0]=[];
    //$dbImportTable[1]=[];
    //--------------------------------------------------------------
    echo "<h1>Loop 1: create array with index</h1>";
    // loop through lines
    //ini_set("memory_limit","10M");
    foreach ($table as $idx=>$row){
        //dispArray($row)."<br>row above<br>";
        if ($idx!=0){
            if ($row[1]!=""){ // make sure date exists...columns starts at 0
                    $dbRow=[];
                    // loops through all the columns
                    foreach ($colMatchNumber as $key=>$col){
                        //$input[$key]=$row[$col];
                        $dbRow[$key]=$row[$col-1];
                    }
                        //print_r($dbRow);
                    //var_dump($row);

                    if($dbRow['oldStock']==""){ // replace empty row by 0
                        $dbRow['oldStock']=0.0;
                    }
                    if($dbRow['ajout']==""){ // replace empty row by 0
                        $dbRow['ajout']=0.0;
                    }

                    $UNIX_DATE = ($dbRow['thedate'] - 25569) * 86400;
                    $dbRow['thedate']=gmdate("Y-m-d", $UNIX_DATE);
                    array_push($dbImportTable,$dbRow);
                    if ($dbRow['ean']=='2000000003085'){
                        dispArray($dbRow);
                        echo $idx;
                    }
                    //echo "<br>";
                
            }
        }
    }
    echo "il y a $idx lignes<br>";
    echo "ligne 2<br>";
    dispArray($dbImportTable[2]);
    //var_dump($dbImportTable);
    //--------------------------------------------------------------
    // check famille, departement, fournisseur in $dbImportTable to 
    //update families

    echo "<h1>Loop 2: import in database</h1>";
    //var_dump($dbImportTable[3]);
    //die;
    $searchKeys=['ean'];
    //----------------------------------------------------------------------
    // Put in database prod_articles
    //$try=0;
    //echo "<h2>Les produits</h2>";
    //dispArray($colMatch);        
    //$filter=array_keys($colMatch);
    //echo "filtre avant<br>";
    //dispArray($filter); 
    //unset($filter[14]); // take out stock
    //unset($filter[9]); // take out fournisseurName
    //unset($filter[7]); // take out familyName
    //unset($filter[5]); // take out departementName

    //echo "filtre apres<br>";
    //dispArrayVals($filter);
    //dispArray($dbImportTable[0]);
    //var_dump($dbImportTable);
    //importFromTableKeys($dbImportTable,'prod_articles',$try,$searchKeys,'ean',$filter);
    //----------------------------------------------------------------------
    // Put in database prod_prices
    echo "<h2>Les stocks</h2>";
    dispArray($colMatch);        
    //$filter=['ean','stock','source','thedate','user'];

    // add field source et date
        foreach ($dbImportTable as $key=>$row){
                $dbImportTable[$key]['source']=$target_file;
                //$dbImportTable[$key]['thedate']=$_REQUEST['importDate'];
                $dbImportTable[$key]['user']=$userId;
        }
        //$dbImportTable[0][12]="source";
        //$dbImportTable[0][13]="thedate";
    $filter=array_keys($dbImportTable[0]);
    //
    echo "filter is <br>";
    //var_dump($dbImportTable);
    dispArrayVals($filter);
    //dispArray($dbImportTable[0]);
    echo "ligne 2<br>";
    dispArray($dbImportTable[788]);
    echo "try=".$try."<br/>";
    $searchKeys=['ean','thedate','bl','raison'];
    dispArray($dbImportTable[1]);
    importFromTableKeys($dbImportTable,'prod_stock_mvt',$try,'id',$searchKeys,$filter);

    //var_dump($dbImportTable);
    //----------------------------------------------------------------------
    // Save
    //$workBook = new Spreadsheet();
    //$sheet = $workBook->getActiveSheet(); 
    //array_to_xls($dbImportTable,$sheet);
    //$writer = new Xls($workBook);   
        
    //$writer->save('files/liste.xls');
}     
?>


