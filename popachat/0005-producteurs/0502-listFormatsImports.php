<?php
$query="SELECT * FROM prod_import_readxls order by format, rank,col";
$table=query_table($query);

class Cell
{
    public $col;
    public $row;
    public $comment;

}



// display formats
$array=[];
for ($i=1;$i<sizeof($table);$i++){
    $row=$table[$i];
    //dispArray($row);
    $formatNb=$row['format'];
    if(!isset($array[$formatNb])){$array[$formatNb]=[];}
    $newCell=new Cell;
    $newCell->row=$row['col'];
    $newCell->col=$row['row'];
    $newCell->comment=$row['comment'];
    $array[$formatNb][$row['keyword']]=$newCell;
}
$format=$array[1];

echo "<table class='xlsformat'>";
foreach ($array as $key=>$format){
    echo "<th span='2'>$key</th>";
    echo "<tr></tr>";

    foreach ($format as $idx=>$key){
        echo "<tr><td>".$idx."</td><td>".$key->col."</td><td>".$key->row."</td><td>".$key->comment."</td></tr>";
    }

}
echo "</table>";
?>
