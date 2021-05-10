<?php
//echo "import";
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
    $inputCol=['ean'=>4,'refFour'=>5,'designation'=>6,'tva'=>18];
    
    $take=['ean'=>1,'refFour'=>1,'designation'=>1,'tva'=>1];
    // Run through each line
    
    for ($row=1;$row<sizeof($csv)-1;$row++){
        $insert=[]; // where values will be stored
        $line=$csv[$row];  // readline
        //echo "<br/>$row -";
        //print_r($line);
        foreach($inputCol as $key=>$col){
            if ($take[$key]){
                $val=$line[$col];
                $val=strip_spaces($val); // make share no leading or trailing spaces
                //print("X".$key."X".$val."X<br>");
                //print("X".$key." ".$val."X<br>");
                $insert[$key]=$val;
            }
        }
        $prix=strip_spaces($line[22]);
        $prix=str_replace(",",".",$prix);
        $insert['prixAchat']=$prix;
        $insert['quantite']=strip_spaces(str_replace(",",".",$line[11]));
        
        // look in data base:
        $refFour=$insert['refFour'];
        $ean=str_replace(" ","",$insert['ean']); // suppress all space for empty id
        //echo "eanfirst".$ean;
        $databaseKey=['ean','departement','tva','famille','conditionnement','contenance','unite'];
        
        // Try for ean
        if ((array_key_exists($ean,$eanAliasDico))&&($ean!="")){ // Ean defined
            $ean=$eanAliasDico[$ean];
            //echo "alias for ".$ean." has been found<br>";
            //var_dump($line);
            foreach($databaseKey as $col){
                //print($produitrefFourDico[$refFour]["ean"]);
                //echo "_".$ean."_";
                $insert[$col]=$produitEanDico[$ean][$col];
                $filter=['ean','departement','tva','famille','conditionnement'];
                $query=create_UPDATE('prod_articles',$insert,$filter,'ean');
                //echo $query;
                simple_query($query);
                //echo "finish<br>";
            }
        }
        else{
            //echo "ean not found try refFour";
            //echo "refFour=".$refFour;
            if (array_key_exists($refFour,$refFourDico)){ // Ref Four defined
                $ean=$refFourDico[$refFour];
                //echo "I found ".$refFour;
                foreach($databaseKey as $col){
                    //print($produitrefFourDico[$refFour]["ean"]);
                    //echo $col;
                    $insert[$col]=$produitrefFourDico[$refFour][$col];
                    $filter=['ean','departement','tva','famille','conditionnement'];
                    $query=create_UPDATE('prod_articles',$insert,$filter,'refFour');
                    //echo $query."<br>";
                    simple_query($query);
                }
            }
            else{// no ref nor ean
                    echo "nothing not found<br>";
                    echo $insert['ean']." ";
                    echo $insert['designation']." ";
                    echo $insert['prixAchat']." ";
                    echo $insert['refFour']."<br>";
                    $filter=['ean','designation','refFour','fournisseur'];
                    $query=create_INSERT('prod_articles',$insert,$filter);
                    echo $query;
                    //simple_query($query);
            }
        }
        
        // Creation Command
        
        
        $insert['commande_id']=$order['commande'];
        
        //print_r($insert);
        $filter=['commande_id','ean','quantite','prixAchat'];
        $query=create_INSERT('prod_commandeList',$insert,$filter);
        simple_query($query);
        //print_r($insert);
        //echo "$query<br>";
    }
?>
