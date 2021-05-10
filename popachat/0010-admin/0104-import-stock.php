<?php
    $col=[];
    $colMatch=['ean'=>'A','refFour'=>'B','designation'=>'C','tva'=>'AG','departement'=>'M','departementName'=>'N','famille'=>'O'];
    $colMatch=array_merge($colMatch,['familleName'=>'P','fournisseur'=>'S','fournisseurName'=>'T']);
    $colMatch=array_merge($colMatch,['uniteContenance'=>'AL','conditionnement'=>'AJ','prixAchat'=>'E','prixVente'=>'H','stock'=>'D']);
    
    //------------
    // define $colMatchNumber
    $colMatchNumber=['tva'=>33,'uniteContenance'=>38,'conditionnement'=>36];
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
    //dispArray($colMatchNumber);
    //-------------------
    
    // import excel
    //die;
    $thedate=$_REQUEST['importDate'];
    echo "La date utilisée est $thedate et vient de l'affichage<br>";
    echo "Import Stock chosen<br>";
    if ($target_file!=""){
    $table=import_xls($target_file,$try);
    //------------------------------------------------------------------
    
    //var_dump($table);
    // create first line
    $dbImportTable=[];
    //$dbImportTable[0]=[];
    //$dbImportTable[1]=[];


    
    //foreach($colMatch as $key=>$val){
    // array_push($dbImportTable[0],$key);   
    //}
    //var_dump($dbImportTable,$dbImportTable[0]);
    //--------------------------------------------------------------
    // create table with keyword of database as key
    //array_push($dbImportTable,$dbImportTable[0]);
    //--------------------------------------------------------------
    echo "<h1>Loop 1: create array with index</h1>";
    // loop through lines
    //ini_set("memory_limit","10M");
    foreach ($table as $key=>$row){
        //dispArray($row)."<br>row above<br>";
        if ($key>0){
            if ($row[0]!=""){
                $dbRow=[];
                // loops through all the columns
                foreach ($colMatchNumber as $key=>$col){
                
                    //$input[$key]=$row[$col];
                    $dbRow[$key]=$row[$col-1];
                    }
                }
                //print_r($dbRow);
                //$dbRow['marque']=(1-$dbRow['prixAchat']/$dbRow['prixVente'])*100;
                if($dbRow['stock']==""){ // replace empty row by 0
                    $dbRow['stock']=0.0000001;
                }
                array_push($dbImportTable,$dbRow);
                //echo "<br>";
            }
        }
    }
    echo "ligne 2<br>";
    dispArray($dbImportTable[2]);
    //var_dump($dbImportTable);
    //--------------------------------------------------------------
    // check famille, departement, fournisseur in $dbImportTable to 
    //update families
    echo "<h1>Loop 2 : Check famille,departement,fournisseur</h1>";
    //$attr=["departement"=>"prod_departement","famille"=>"prod_famille","fournisseur"=>"prod_fournisseur"];
    $attr=["departement","famille","fournisseur"];

    
    //var_dump($dbImportTable);
    
                // create dictionnary from database for keyword $key
               // $dquery="SELECT * FROM $dval;";
                //$dtable=query_table($dquery);
                //$dico=create_one_field_dictionnary($dtable,"titre","id");
    foreach ($dbImportTable as $key=>$row){
        foreach($attr as $dkey=>$dval){
            //echo "coucoubon";
            //echo $key."<br>";
            //dispArray($row);
            
            //var_dump($dico);
            if (!in_array($row[$dval."Name"],$dico[$dval])){
                    if ((($row[$dval])!="") &&($row[$dval."Name"]!="")){
                        //echo "dval=".$dval." does not exist".$row[$dval."Name"]." ".$row[$dval."Name"];
                        $query="INSERT INTO prod_$dval (id,titre) VALUES ('".$row[$dval]."','".$row[$dval."Name"]."');";
                        echo $query;
                        simple_query($query);
                        $dico[$dval][$row[$dval]]=$row[$dval."Name"];
                        //print_r($dbImportTable[$i]);
                        echo "<br><br>";
                    }
            }
        }
    }
    //echo "<br><br>";
    //array_shift($dbImportTable);
    
    //var_dump($dbImportTable[1]);
    $filter=array_keys($colMatch);
    // correct departement
    
    
    
    echo "<h1>Loop 3: import in database</h1>";
    //var_dump($dbImportTable[3]);
    //die;
    $searchKeys=['ean'];
    //----------------------------------------------------------------------
    // Put in database prod_articles
    //$try=0;
    echo "<h2>Les produits</h2>";
    //dispArray($colMatch);        
    $filter=array_keys($colMatch);
    //echo "filtre avant<br>";
    //dispArray($filter); 
    unset($filter[14]); // take out stock
    unset($filter[9]); // take out fournisseurName
    unset($filter[7]); // take out familyName
    unset($filter[5]); // take out departementName
    
    //echo "filtre apres<br>";
    //dispArrayVals($filter);
    //dispArray($dbImportTable[0]);
    //var_dump($dbImportTable);
    importFromTableKeys($dbImportTable,'prod_articles',$try,'ean',$searchKeys,$filter);
    //----------------------------------------------------------------------
    // Put in database prod_prices
    echo "<h2>Les stocks</h2>";
    dispArray($colMatch);        
    $filter=['ean','stock','source','thedate','user'];
    
    // add field source et date
        foreach ($dbImportTable as $key=>$row){
                $dbImportTable[$key]['source']=$target_file;
                $dbImportTable[$key]['thedate']=$_REQUEST['importDate'];
                $dbImportTable[$key]['user']=$_SESSION['userInfo']['userId'];
        }
        //$dbImportTable[0][12]="source";
        //$dbImportTable[0][13]="thedate";
    //
    echo "filter is <br>";
    //var_dump($dbImportTable);
    dispArrayVals($filter);
    //dispArray($dbImportTable[0]);
    echo "ligne 2<br>";
    dispArray($dbImportTable[2]);
    echo "try=".$try."<br/>";
    $searchKeys=['ean','thedate'];
    importFromTableKeys($dbImportTable,'prod_stock',$try,'id',$searchKeys,$filter);

    //var_dump($dbImportTable);
    //----------------------------------------------------------------------
    // Save
    $workBook = new Spreadsheet();
    $sheet = $workBook->getActiveSheet(); 
    array_to_xls($dbImportTable,$sheet);
    $writer = new Xls($workBook);   
?>
