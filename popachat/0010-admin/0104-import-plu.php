<?php
    echo "PLU chosen<br>";
        echo "in 500-miscellaneousFunctions.py, year is 2021<br>";
        
        $thedate=$_REQUEST['importDate'];
        [$thedate,$year,$ext]=extractDateYearAndExtensionFromFilename($target_file);
        
        echo "Import Articles chosen<br>";
        echo "the Date is $thedate<br>";
        echo "in 500-miscellaneousFunctions.py, year is 2021 and actual year is $year<br>";
        echo "theExtension is $ext<br>";
        //$array=import_xlsx($target_file,$try); // get imports
        $array=import_xls($target_file,$try); // get imports
        //------------------------------------------------------------------
        // defines column to import
        $colMatch=['thedate'=>'A','ean'=>'B','refFour'=>'C','designation'=>'D','prixVente'=>'E','prixVentettc'=>'F',
        'quantite'=>'G','cattc'=>'H','caht'=>'I','marque'=>'K','ratio'=>'L','marge'=>'M','stock'=>'N',
        'prixAchat'=>'O','tva'=>'P','departement'=>'Q','famille'=>'R','fournisseur'=>'T'];

       //   $colMatch=array_merge($colMatch,['conditionnement'=>'P','prixAchat'=>'T','prixVente'=>'U','stock'=>chr(ord('Z')+3)]);
        $dico=importXlsArrayToDictionnary($array,$colMatch);
        //
        // add values and correct values
        
        foreach ($dico as $key=>$row){ // loop over line
            $dico[$key]['author']=$_SESSION['userInfo']['userId'];
            $dico[$key]['thedate']=decode_date_byName($row['thedate']);
            if ($row['tva']==5.5){$dico[$key]['tva']=1;}
            else{if ($row['tva']==20){$dico[$key]['tva']=2;}
                else{echo "For ".$dico[$key]['ean'].":".$dico[$key]['designation']." tva is ".$dico[$key]['tva'];}
            }
            //echo "thedate".$dico[$key]['thedate'];
        }
        
        // prepare for import
        $primary='id';
        $searchKeys=['thedate','ean'];
        
        $tableName='prod_plu';
        $filter=array_keys($colMatch);
        dispArray($filter);
        unset($filter[12]);
        unset($filter[15]);
        unset($filter[16]);
        unset($filter[17]);
        dispArray($filter);
        uploadIntoDatabase($dico,$colMatch,$primary,$searchKeys,$filter,$tableName,$try,'ean');
        
        
?>
