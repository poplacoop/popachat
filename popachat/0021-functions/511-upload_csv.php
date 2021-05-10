<?php
$try=0;
if ($order['upload_csv']==2){
    $try=1;
    echo "Essai<br>";
}
else{
    if ($order['upload_csv']==2){
        echo "Modification de la base<br>";
    }
}
//------------------------------------------------------------------
// get File and save it
//------------------------------------------------------------------

    $target_dir = "./files/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
    $filename=$_FILES["fileToUpload"]["name"];
if (!$try){
    // load into database
    $query="INSERT INTO `prod_import_files` (filename, author) VALUES ( '".$filename."', '$userId');";
    simple_query($query);
}



//------------------------------------------------------------------
$csv = $target_file;
$csv = read($csv);
//print_r($csv);
$tableName=$csv[0][0];
$headers=$csv[1];

//$head=['Code barre','Ref fournisseur','DEPARTEMENT','TVA','FAMILLE','Désignation','CONDITIONNEMENT','CONTENANCE','UNITe','Prix achat net','Prix de vente TTC','stock','stock','Fournisseur'];
//$output=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat','prixVente'];
//$inputCol=['ean'=>4,'refFour'=>5,'designation'=>6,'tva'=>18];

//$take=['ean'=>1,'refFour'=>1,'designation'=>1,'tva'=>1];
// Run through each line
for ($row=2;$row<sizeof($csv)-1;$row++){
    $insert=[]; // where values will be stored
    $line=$csv[$row];  // readline
    //echo "<br>row=$row<br>";
    //print_r($line);
    //echo "<br>";
    $keyVal=$line[0];
    
    //echo "keyval=$keyVal X";
    $keyName=$headers[0];
    
    $query="SELECT * FROM $tableName WHERE ".$headers[0]."='".$keyVal."';";
    $table=query_table($query);
    //print_r($table);
    
    // Prepare date for update or create
    $input=[];
    $filter=[];
    for($k=0;$k<sizeof($headers);$k++){
        if ($line[$k]!=""){
            $input[$headers[$k]]=$line[$k];
            if ($k>0){
                array_push($filter,$headers[$k]);
            }
        }
    }
    //var_dump($input);
    $whereKey=$headers[0]; 
    //displayTableInHtml($table); 
    //echo "size=".sizeof($table);
    // case where the id does not exist
    if (sizeof($table)!=2){
        //echo "table ok<br>";
        if (sizeof($input)>0){
            echo "La ligne avec la référence $keyVal:".$whereKey." va être créée dans '$tableName'<br>";
            array_push($filter,$whereKey);
            //echo "does not exist<br>";
            //echo "size=".sizeof($input);
            $query=create_INSERT($tableName,$input,$filter,$whereKey); 
            echo $query."<br>";
        }
    }
    else{
        //echo "exists<br>";
        $col=$table[1];
        //var_dump($col);
        foreach ($input as $key=>$val){
            //echo "<br>".$key."=>".$val;
            //echo " ".$col[$key];
            //echo "égalité?".(stripslashes($val))."=".stripslashes($col[$key]);
            if(stripslashes($val)!=stripslashes($col[$key])){
                echo "Pour l'attribut '$whereKey:$keyVal' la valeur '".$col[$key]."' est remplacé par '".$val."'<br>";
            }
            else{
                //echo "égalité";
            }
        }
        
        
        $query=create_UPDATE($tableName,$input,$filter,$whereKey);
        }
       
    //echo "<br>".$query."<br>";
    if (!$try){simple_query($query);}

}

?>

