<?php

function openLink(){
    //phpinfo();
    $link = mysqli_connect(ini_get("mysqli.default_host"),ini_get("mysqli.default_user"),
                    ini_get("mysqli.default_pw"),'mypop');
    if (!($link)){
    echo "<p> Echec de Connection</p>";
    }
    return $link;
}
function closeLink($link){
   mysqli_close($link);  
}


function simple_query($query){
    $link=openLink();
    // log
    if (substr($query,0,6)!="SELECT"){
        $author="";
        if (isset($_SESSION['userInfo']['userId'])){if (isset($_SESSION['userInfo']['userId'])){$author=$_SESSION['userInfo']['userId'];}}
        $snipeQuery="INSERT INTO sql_record (sqlstring,author) VALUES('".addslashes(htmlentities($query))."',".$author.");";
        if (!($re=mysqli_query($link,$snipeQuery)))
            {echo "<br>simple_query error log with:$snipeQuery<br>";}
        
    }
    // sql query
    if (!($r=mysqli_query($link,$query)))
        {echo "<div class='error'>error with:$query</div>";}
    return $r;

    
    closeLink($link);
}

function json_query($query){
    $link=openLink();
    
    if (!($r=mysqli_query($link,$query)))
        {echo "error with:$query";}
    else{
        while ($row=mysqli_fetch_array($r)){
            $data = json_encode($row);
        }
    }
        
    return $data;
}

function query_table($query,$head=1){
    $link=openLink();
    $tbl=[];
    
    // log
    if (substr($query,0,6)!="SELECT"){
        $author="NULL";
        if (isset($_SESSION['userInfo']['userId'])){if (isset($_SESSION['userInfo']['userId'])){$author=$_SESSION['userInfo']['userId'];}}
        //echo "author=".$author;
        $snipeQuery="INSERT INTO sql_record (sqlstring,author) VALUES('".addslashes(htmlentities($query))."',".$author.");";
        //echo $snipeQuery;
        if (!($r=mysqli_query($link,$snipeQuery)))
            {echo "<br>query_table error log with:$snipeQuery<br>";}
        
    }
    // sql query
    
    
    if (!($r=mysqli_query($link,$query)))
        {if($_SESSION['userInfo']['admin']==1){echo "error with:<br><br>$query<br><br>";}}
    else{
        if ($head){
            $headline=[];
            while ($fieldinfo = mysqli_fetch_field($r)) {
                array_push($headline,$fieldinfo -> name);
            }
            array_push($tbl,$headline);
        }
        while ($ligne=mysqli_fetch_array($r)){
            /*$line=[];

            foreach($ligne as $cell){
                //$cell=$ligne[$i];
                //print($cell."<br>");
                array_push($line,$cell);
            }
        //print($line);*/
            array_push($tbl,$ligne);
        }
    }
    closeLink($link);
    //var_dump($tbl[0]);
    return($tbl);
}
//-----------------------------------------------------------------------
// query table dico
// convert table from query to dico.
function query_table_dico($query){
    $link=openLink();
    $tbl=[];
    
    if (!($r=mysqli_query($link,$query)))
        {echo "error with:<br><br>$query<br><br>";}
    else{
        
        $headline=[];
        while ($fieldinfo = mysqli_fetch_field($r)) {
            array_push($headline,$fieldinfo -> name);
        }
        //array_push($tbl,$headline);
        //echo "headline";
        //dispArray($headline);
        
        $line=[];
        while ($ligne=mysqli_fetch_array($r)){
            //dispArray($ligne);
            foreach ($headline as $val){
                $line[$val]=$ligne[$val];
            }
            array_push($tbl,$line);
        }
    }
    closeLink($link);
    //var_dump($tbl[0]);
    return($tbl);
}
//----------------------------------------------------------------------
// retreive information for one ligne in database
//----------------------------------------------------------------------
function getFirstLine($sql){
    $table=query_table($sql);
    if (sizeof($table)>=2){
        return $table[1];
    }
    else{
        return "";
    }
    
}
// Display in HTML
//----------------------------------------------------------------------
function displayinhtml($tbl,$class="",$headline=1){
    echo "<table class=$class>\n";
    
    if ($headline){
        $row=$tbl[0];
        for($i=0;$i<sizeof($row);$i++){
            
            $cell=$row[$i];
            echo "<th>$cell</th>";
        }
    }
    
    for($n=1;$n<sizeof($tbl);$n++){
        $row=$tbl[$n];
        echo "  <tr>";
        
        for($i=0;$i<sizeof($row)/2;$i++){
            $cell=$row[$i];
            echo "<td>$cell</td>";
            
        
        
            //foreach($row as $cell){
            //    echo "<td>$cell</td>";
        }
        echo "</tr>\n";
    }
    
    print "</table>\n";   
}
//----------------------------------------------------------------------
// display Query
//
function display_query($query){
    //$query="SELECT `uniteContenance` FROM `prod_articles` group by uniteContenance";
    $table=query_table($query);
    displayinhtml($table);
}

//----------------------------------------------------------------------
// Display in HTML str
//----------------------------------------------------------------------
function displayTableInHtmlStr($tbl,$headline=1,$class="",$id="",$headers=[],$filter=[]){
    if ($class!=""){
        $class="class='$class'";
    }
    if ($id!=""){
        $id="id='$id'";
    }
    
    $str= "<table $id $class>\n";
    if($headers!=[]){
        $str.="<tr>";
        foreach ($headers as $cell){
            $str.="<th>$cell</th>";
        }
        $str.= "</tr>";
        
    }
    else{
        if ($headline){
            $str.= "</tr>";
            $row=$tbl[0];
            for($i=0;$i<sizeof($row);$i++){
                $cell=$row[$i];
                $str.="<th>$cell</th>";
            }
            $str.= "</tr>";
        }
    }
    
    for($n=1;$n<sizeof($tbl);$n++){
        $row=$tbl[$n];
        $str.= "  <tr>";
        //var_dump($filter);
        //var_dump($row);
        if ($filter==[]){
            for($i=0;$i<sizeof($row)/2;$i++){
                //if (in_array($row
                $cell=$row[$i];
                $str.= "<td>$cell</td>";
            }
        }
        else{
            foreach($filter as $key){
                //echo "key=".$key."H<br>";
                if (array_key_exists($key,$row)){
                    $cell=$row[$key];
                    $str.= "<td>$cell</td>";
                }
            }
            $str.= "</tr>\n";
        }
    }
    
    $str.= "</table>\n";
    return $str;   
}

//----------------------------------------------------------------------
// input is dictionnary {'ean':35}  -> insert ean='35'
function create_INSERT($sqlTable,$table,$filter){
    $attributeName="(";
    $attributeValue="(";
    $comma="";
    
    foreach ($table as $key=>$val){
        //echo $key,$val;
        if (in_array($key,$filter)){
            $attributeName.=$comma.$key;
            $attributeValue.=$comma."'".addslashes(htmlentities($val))."'";
            $comma=",";
        }
    }
    $attributeName.=")";
    $attributeValue.=")";

    $query="INSERT INTO `$sqlTable` ".$attributeName." VALUES ".$attributeValue;
    return $query;
}
//----------------------------------------------------------------------
// input 
// $sqlTable is the name of the mysql table to update
// $keytable is a dictionnary {'ean':35}  -> set ean='35'
// $filter is key to select $filter=['ean','id']
// $whereKey it the identification key 'ean'
function create_UPDATE($sqlTable,$keytable,$filter,$primary){
    $query="update $sqlTable SET ";
    $comma="";
    foreach ($keytable as $key=>$val){
        //echo "key=$key,val=$val";
        //print_r($filter);
        if (in_array($key,$filter)){
            //echo "insde";
            $query.= "$comma $key='".addslashes($val)."' ";
            $comma=",";
        }
    }
    $query.=" WHERE ".$primary."='".$keytable[$primary]."';";

    
    return $query;
}

//----------------------------------------------------------------------
// Columns are put in first line
// returns an array.
function read($csv){
    $file = fopen($csv, 'r');
    while (!feof($file) ) {
        $line[] = fgetcsv($file, 1024,$delimiter=";");
    }
    fclose($file);
    return $line;
}


//----------------------------------------------------------------------
// Create dictionnary from table from sql query
// $key is the string for the key and $val is the string for the value
// width table with headers
function create_one_field_dictionnary($table,$key,$val,$start=1){
    // $table is the table of results
    // $key is the key of the dictionnary
    // $val is the value of the dictionnary
    // $start is where to start the dictionnary (line 0 if no header or 1 if header)
    //var_dump($table);
    if ($table==""){
        return [];
    }
    else{ 
        $dico=array();
        for($i=$start;$i<sizeof($table);$i++){
            $row=$table[$i];
            
            //var_dump($row);
            //echo "<br>";
            $dico[strip_spaces($row[$key])]=$row[$val];
        }
        //$dico[""]="";
        return $dico;
    }
}

function create_one_field_dictionnary_sql($query,$key,$val){
    $table=query_table($query);
    return create_one_field_dictionnary($table,$key,$val);
}

//----------------------------------------------------------------------
// Create dictionnary from table from sql query
// $table comes from query_table(): array with keys...
// $key is the string for the key of each line
// and $val is the string for the value
function create_product_dictionnary($table,$key){
    $dico=array();
    for($i=1;$i<sizeof($table);$i++){
        $row=$table[$i];
        $dico[strip_spaces($row[$key])]=$row;
    }
    //$dico[""]="";
    return $dico;
}
function create_product_dictionnary_sql($query,$key){
    $table=query_table($query);
    return create_product_dictionnary($table,$key);
}


//----------------------------------------------------------------------
// Create dictionnary from table from sql query
// $table comes from query_table(): array with keys...
// $key is the string for the key of each line
// and $val is the string for the value
function create_product_dictionnary_dico($table,$key){
    $dico=array();
    for($i=1;$i<sizeof($table);$i++){
        $row=$table[$i];
        foreach ($row as $idx=>$val){ 
            if (is_numeric($idx)==0){
                //echo "<br>".$idx."=".$val;
                $dico[strip_spaces($row[$key])][$idx]=$val;
                
            }
        }
        //$dico[strip_spaces($row[$key])]=$row;
    }
    //$dico[""]="";
    return $dico;
}





  
?>

