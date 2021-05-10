<?php

//----------------------------------------------------------------------
// Import table into Database with keys
//----------------------------------------------------------------------
    
function importFromTableKeys($table,$tableName,$try,$primary,$searchKeys,$generalFilter=[],$compulsory=""){
    // import from table where rows are dictionnaries
    // if line exists, update the database, if not insert into the database.
    // $seachkeys=['ean','refFour'] are the keys to base identify on 
    // $tableName is the name of the table "prod_plu"
    // $primary is the "primary field" to identify line.
    // if $try is 1 then no update is done
    // $table starts at line 0 (noHeaders)
    // $generalFilter is an array with the keys required for the table. If not given all the keys give in the dico
    // $compulsory are the key we must have
    //if empty all the keys of the dictionnary
	$display=0;    
    
    
    if ($generalFilter==[]){
        $generalFilter=array_keys($table[0]);
    }
    if($display){dispArray($generalFilter);};
    //echo "<br>";

    $primaryName=$primary; // primary key
    //echo "la clé primaire est $primary<br>";
    
    $headers=array_keys($table[0]);
    if($display){echo "Les attributs de recherche<br>";
    	dispArray($searchKeys); // found position of search
 	}
    
	if($display){    
    	echo "<h2>LOOP</h2>";
 	}
    //echo sizeof($table);
    for ($i=0;$i<sizeof($table);$i++){
			if($display){        
        		echo $i."-";
        	}
        
        $insert=[]; // where values will be stored
        //--------------------------------------
        // Prepare dictionnary for update or create
        // $input holds attributes values and $filter attributes list.
        $input=[];
        $line=$table[$i];  // readline
        //dispArray($line);
        // create a dictionnary for input
        // loop through all values only for those in $generalFilter
        foreach($line as $key=>$val){
            if(($val!="")&&(in_array($key,$generalFilter))){  // make sure value is not empty and key in filter
                $input[$key]=addslashes($val);
            }
        }
        //dispArray($input);
        //$ean="20000054";
        //if ($input['ean']==$ean){dispArray($input);}
        // search for keys in database: is value present?
        $where="";
        foreach ($searchKeys as $idx=>$val){
            //echo $val;
            //print_r($line);
            $where.=" AND ".$val."='".$line[$val]."'";
        }
        $query="SELECT * FROM $tableName WHERE 1 $where";
        //if ($input['ean']==$ean){echo $query;}
        $resultTable=query_table($query);
        if (($compulsory!="")&&(!isset($input[$compulsory]))){$input[$compulsory]=0;}
        $query="";
        // case where the id does not exist
        if (sizeof($resultTable)==1){ // did not find value.
            
            $filter=$generalFilter; //filter is set to maximum line will be added
            if ((sizeof($input)>0)&&((isset($input[$compulsory]))||($compulsory==""))){ // there is at least one value which is not empty
                echo "La ligne avec les informations va être créée dans '$tableName'<br>";
                if (in_array($primary,$headers)){
                    array_push($filter,$primaryName); // add primary if in database
                }
                $query=create_INSERT($tableName,$input,$filter); 
                //echo $query."<br>";
            }
            else{
                echo "input est vide ou $compulsory est vide";
                dispArray($line);
                echo "<br>";
            }
        }
        else{
            //if ($input['ean']==$ean){echo "value exists";}
            //the value exists there are two lines: header and values
            $filter=[]; //filter is empty and get filled if value are differents
            $col=$resultTable[1]; // data found
            //dispArray($col);
            //if ($input['ean']==$ean){echo $compulsory;}
            if ((isset($input[$compulsory])||($compulsory==""))){
                //if ($input['ean']==$ean){echo "on entre";}
                //dispArray($input);
                foreach ($input as $key=>$val){  // loop through key of input to check match in database
                    $add=1; // by default
                    //echo "key=".$key;
                    if(stripslashes($val)==stripslashes($col[$key])){ // if equal does not modify
                        $add=0;
                        //echo "same";
                    }   
                    //
                    //if ($input['ean']==$ean){echo "<br>".$key." $val equal ".$col[$key]." add=$add<br>";}             
                    if ((stripslashes($val)!="")){ // if value is not ""
                        if (abs(floatval(stripslashes($val))-floatval(stripslashes($col[$key])))<0.001*abs(floatval(stripslashes($val)))){
                                 $add=0; // no major difference 0 also
                        }
                    }
                    //if ($input['ean']==$ean){echo "<br>".$key." $val equal ".$col[$key]." add=$add<br>";}         
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
            }
            //echo "filter ready<br>";
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
        //echo $query."<br>";
        if ($query!=""){
            if (!$try){
                simple_query($query);
            }
            else{
                echo " essai :";
            }
            echo $query."<br>";
        }

    }
    //echo "coucou";
}
?>
