<?php
@session_start();
include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0501-graphFunctions.php";
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);


// get Query
$query=$_REQUEST['query'];
$query=substr($query,1,strlen($query)-3);
$stockQuery="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
            FROM `prod_stock` where not isnull(stock) group by ean) as last 
            left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
            ORDER BY `last`.`thedate` ASC";
$query="select article.ean,designation,stock from ($query) as article left outer join ($stockQuery) as stocktbl on article.ean=stocktbl.ean";

$table=query_table($query,1);
//displayinhtml($table);


include "../0021-functions/0506-exportToXls.php";
$str=exportToXls("inventaire.xls",$table,["inventaire"]);
echo "<a href='./files/inventaire.xls'>inventaire.xls</a>";
echo $str;
// create Excel and html
?>
