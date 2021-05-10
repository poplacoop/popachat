<?php
//echo "import";
    $displayLevel=3; // 5 is full disp, 4 drop if ean found, 3 drop ean and refour found, 2 update and creation, 1 creation only
    echo "<br>";

    include "../0010-admin/0510-imports_functions.php";
    include "../0010-admin/0511-imports_fromTableKeysFunction.php";

    $userId=$_SESSION['userInfo']['userId'];
    $target_dir = "./files/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);

    $filename=$_FILES["fileToUpload"]["name"];
    // update commande file
    $query="UPDATE prod_commande SET file='.$filename.' WHERE id='$commandeId'";
    simple_query($query);
    $query="DELETE FROM prod_commandeList WHERE commande_id='$commandeId';";
    simple_query($query);   

    $csv = $target_file;
    $csv = read($csv);
    //print_r($csv);
    
    $head=['Code barre','Ref fournisseur','DEPARTEMENT','TVA','FAMILLE','Désignation','CONDITIONNEMENT','CONTENANCE','UNITe','Prix achat net','Prix de vente TTC','stock','stock','Fournisseur'];
    $output=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat','prixVente'];
    $inputCol=['ean'=>4,'refFour'=>5,'designation'=>6,'tva'=>18,'colis'=>10,'quantite'=>11,'unite'=>7];
    
    $take=['ean','refFour','designation','tva','colis','prixAchat','quantite']; // columns to retrieve
    //------------------------------------------------------------------
    // Run through each line
    
    for ($row=1;$row<sizeof($csv)-1;$row++){
        
        $input=[]; // where values will be stored
        $line=$csv[$row];  // readline
        //echo "<br/>$row -";
        //print_r($line);
        
        // put all values in $input
        foreach($inputCol as $key=>$col){
            if (in_array($key,$take)){
                $val=$line[$col];
                $val=strip_spaces($val); // make share no leading or trailing spaces
                //print("X".$key."X".$val."X<br>");
                //print("X".$key." ".$val."X<br>");
                $input[$key]=$val;
            }
        }
        
        // Check if $ean exists
        //var_dump($eanAliasDico);
        //echo "<br><br>";
        if(array_key_exists('ean',$input)){
            // check if alias must be used.
            if (array_key_exists($input['ean'],$eanAliasDico)){
                $input['ean']=$eanAliasDico[$input['ean']]; // replace by alias
            }
        }
        //else{
            //if ($displayLevel>=3){echo "refFour=".$input['refFour'];}
            
            //check if refFour exists
            if(array_key_exists('refFour',$input)){
                // change to alias 
                if (array_key_exists($input['refFour'],$refFourAliasDico)){
                    $input['refFour']=$refFourAliasDico[$input['refFour']];// replace by alias
                    $input['ean']=$refFourDico[$input['refFour']];            // get back ean
                }
            }
        //}
        
        
        $prix=strip_spaces($line[22]);
        $prix=str_replace(",",".",$prix);
        $input['prixAchat']=$prix;
        $input['quantite']=strip_spaces(str_replace(",",".",$line[11]));
        $input['conditionnement']=floor($input['quantite']/$input['colis']);
        $input['author']=$userId;
        //dispArray($input);
        
        // look in data base:
        $refFour=$input['refFour'];
        $ean=str_replace(" ","",$input['ean']); // suppress all space for empty id
        //echo "eanfirst".$ean;
        $refFour=$input['refFour'];
        
        // contains keyword in the database
        $databaseKey=['ean','departement','tva','famille','conditionnement','contenance','unite','prixAchat','refFour','author'];
        
        if(array_key_exists($ean,$eanAliasDico)){$ean=$eanAliasDico[$ean];} //  replace by alias
        
        $eanFound=0;
        if ($displayLevel>=4){echo "<br>";}
        // Try for ean
        if ((array_key_exists($ean,$produitEanDico))&&($ean!="")){ // Ean defined update article
            $eanFound=1;
        }
        else{
            if ($displayLevel>=4){echo "<br>ean=$ean not found try refFour=$refFour";}
            //echo "<br>refFour=".$refFour;
            if (array_key_exists($refFour,$refFourDico)){ // Ref Four defined
                if ($displayLevel>=4){echo "<br>refFour found";}
                //$refFour=$refFourDico[$refFour]; //  replace by alias
                $input['ean']=$refFourDico[$refFour];
                $ean=$input['ean'];
                $eanFound=1;
            }
            else{
                echo "<br>$refFour not found";
            }    
        }
        
        if ($eanFound){   // update
            if ($displayLevel==5){echo $ean.":".$input['designation']." has been found<br>";}
            $input['ean']=$ean; // replace alias in $input also....
            //var_dump($line);
            $filter=[];
            foreach($databaseKey as $col){
                //print($produitrefFourDico[$refFour]["ean"]);
                //echo "_".$ean."_";
                
                if (isset($input[$col])){
                    if ($input[$col]!=$produitEanDico[$ean][$col]){
                        //echo $produitEanDico[$ean][$col]."<br>";
                        array_push($filter,$col); // select keywords where something has changed
                    }
                }
                //echo "finish<br>";
            }
            if (sizeof($filter)>0){ // update only if there is something to update
                $query=create_UPDATE('prod_articles',$input,$filter,'ean');
                
                if($try==0){
                    simple_query($query);
                }
                else{
                    echo " essai: ";
                }
                echo $query."<br>";
            }
        }
        else{// no ref nor ean
            echo $ean."=".$refFour;
            
            echo "<br>article not found: creation<br>";
            echo $input['ean']." ";
            echo $input['designation']." ";
            echo $input['prixAchat']." ";
            echo $input['refFour']."<br>";
            $filter=['ean','designation','refFour','fournisseur','conditionnement','tva','author','prixAchat'];
            //dispArray($filter);
            //dispArray($input);
            $query=create_INSERT('prod_articles',$input,$filter);
            echo "<br>".$query;
            if($try==0){
                simple_query($query);
            }
            else{
                echo "<h2>essai</h2>";
            }
            echo "<br>";
        }
        
        $input['thedate']=date("Y-m-d");
        $input['source']=$target_file;
        $author=$userId;
        $input['author']=$author;
        
        $filter=['ean','prixAchat','source','thedate','author'];
        //dispArray($input);
        importFromTableKeys([$input],"prod_prices",$try,"ean",["ean"],$filter);
        
        
        
        
        // Creation Command
        
        
        $input['commande_id']=$order['commande'];
        
        //print_r($input);
        $filter=['commande_id','ean','quantite','prixAchat'];
        $query=create_INSERT('prod_commandeList',$input,$filter);
        simple_query($query);
        //print_r($input);
        //echo "$query<br>";
        
    }
?>
