<?php
@session_start();
include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0501-graphFunctions.php";
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);

$col=['prixAchat'=>2,'stock'=>7,'ventes'=>12,'semainedestock'=>13,'commande1periode'=>14,'commande2periodes'=>15,'commande3periodes'=>16,
'introDate'=>17,'productAge'=>18,'excesStockValorise1periode'=>19];
// get Query
$query=$_REQUEST['query'];
//echo $query;
$query=substr($query,1,strlen($query)-3);
$stockQuery="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
            FROM `prod_stock` where not isnull(stock) group by ean) as last 
            left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
            ORDER BY `last`.`thedate` ASC";
            
$query="select article.ean,designation,prixAchat,unitecontenance,contenance,prixAchat/contenance as prixKgL,prixAchat*stock as valeurStockeuros,
stock as stockQuantite,uniteVente,departement,famille,fournisseur 
    from ($query) as article 
    left outer join ($stockQuery) as stocktbl 
    on article.ean=stocktbl.ean ";// change limit

$table=query_table($query,1);
//echo sizeof($table[0]);
//echo "<br>";
//echo sizeof($table[1]);

$query="Select ean,sum(quantite) as ventes from (SELECT * FROM `prod_plu` WHERE thedate > current_date- interval ".$order['myRange']." week ) as a group by ean";
$ventesDico=create_one_field_dictionnary_sql($query,'ean','ventes');
//var_dump($ventesDico['3760091720016']);
$init=1;
foreach ($table as $key=>$row){
    if ($init){
        $init=0;
    }
    else{
        if (array_key_exists($row[0],$ventesDico)){
            $table[$key][$col['ventes']]=$ventesDico[$row[0]];
            $table[$key]['ventes']=$ventesDico[$row[0]];
            //echo "<br>ventes<br>";
        }
        else{
            $table[$key][$col['ventes']]=0;
            $table[$key]['ventes']=0;
        }
        //echo "<br>modified";
        //dispArray($table[$key]);
    }
}


//--------------------------------------------------------------
// retrieve first introduction day from 0503-stockFunctions.php
$whereean="";
//$whereean="AND ean=0022314015198 ";
$myRange=$order['myRange'];
$queryIntroDate="SELECT ean,min(thedate) as minDate,datediff(current_date,min(thedate)) as productAge FROM prod_stock_mvt WHERE LEFT(bl,2)='BL' $whereean GROUP BY ean";
$queryIntroDate="SELECT ean,minDate,if(productAge>".(7*$myRange).",".(7*$myRange).",productAge) as productAge FROM ($queryIntroDate) as A";
$introDateTable=query_table($queryIntroDate,0);
$introDateDico=create_product_dictionnary($introDateTable,"ean"); // convert to dico with ean key
//displayinhtml($introDateTable);
//var_dump($introDateDico);
//dispArray($introDateDico['0022314015198']);
//echo "<br>";
//dispArray($table[2]);
$maxDay=$myRange*7; // defines depth to look for

$stockNoSale=[];

$init=1;
foreach ($table as $key=>$row){
    //echo "row:";
    //dispArray($row);
    //echo "<br>";
    if ($init){
        $init=0;
    }
    else{
        $ean=$row[0];
        $table[$key][$col['semainedestock']]=0;
        $table[$key]['semainedestock']=0;
        $table[$key][$col['commande1periode']]=0;
        $table[$key]['commande1periode']=0;
        $table[$key][$col['commande2periodes']]=0;
        $table[$key]['commande2periodes']=0;
        $table[$key][$col['commande3periodes']]=0;
        $table[$key]['commande3periodes']=0;
        $table[$key][$col['introDate']]=0;
        $table[$key]['introDate']=0;
        $table[$key][$col['productAge']]=0;
        $table[$key]['productAge']=0;
        $table[$key][$col['excesStockValorise1periode']]=0;
        $table[$key]['excesStockValorise1periode']=0;

        if (isset($introDateDico[$ean])){
            $table[$key]['introDate']=$introDateDico[$ean]['minDate'];
            $table[$key]['productAge']=$introDateDico[$ean]['productAge'];
            $table[$key][$col['introDate']]=$introDateDico[$ean]['minDate'];
            $table[$key][$col['productAge']]=$introDateDico[$ean]['productAge'];
            //echo "<br>vente=".$table[$key][$colSales];
            // Si les ventes ne sont pas nulls sur la période
            if ($table[$key][$col['ventes']]!=0){
                //echo "<br>$ean:ventes<br>";
                $weightedSales=$table[$key][$col['ventes']]; // sales
                $weightedSales=$table[$key][$col['ventes']]*$maxDay/$table[$key]['productAge'];
                //echo "sales".$weightedSales;
                
                //echo $table[$key][$col['stock']];
                $table[$key][$col['semainedestock']]=$table[$key][$col['stock']]/$weightedSales;// stock
                $table[$key][$col['commande1periode']]=$weightedSales-$table[$key][$col['stock']];
                $table[$key][$col['commande2periodes']]=2*$weightedSales-$table[$key][$col['stock']];
                $table[$key][$col['commande3periodes']]=3*$weightedSales-$table[$key][$col['stock']];
                if ($table[$key][$col['commande1periode']]<0){
                    $table[$key][$col['excesStockValorise1periode']]=-$table[$key][$col['prixAchat']]*$table[$key][$col['commande1periode']];
                }
                else{
                    $table[$key][$col['excesStockValorise1periode']]=0;
                    
                }
                
            }
            // si les ventes sont nulles sur la période.
            else{
                $table[$key][$col['excesStockValorise1periode']]=$table[$key][$col['stock']]*$table[$key][$col['prixAchat']];
                //echo strtotime(date("Y-m-d"))-strtotime($table[$key][$col['introDate']]);
                if (($table[$key][$col['stock']]!=0)&&((strtotime(date("Y-m-d"))-strtotime($table[$key][$col['introDate']]))>(2*7*24*3600))){
                    $table[$key][$col['semainedestock']]=2*$myRange;
                    array_push($stockNoSale,$table[$key]);
                }
                
            }
        }
        //dispArray($table[$key]);
    }
}
//--------------------------------------------------------------------------------------

foreach ($col as $key=>$val){
    //echo $key."=>".$col[$key]."<br>";
    $table[0][$col[$key]]=$key;
    
}
$stockNoSale[0]=$table[0];

/*echo "table[0]<br>";
var_dump($table[0]);
//dispArray($table[0]);
echo "<br><br>";
displayinhtml($table);
echo sizeof($table[0]);
$nb=0;
echo "<br>";*/
//foreach ($table[0] as $key=>$cell){
//    echo ($nb++)." key".$key."=".$cell."<br>";
//}
//echo $table[0][18];
//echo "<br>";
//echo sizeof($table[1]);
//----------------------------------------------------
// create Excel and html
include "../0021-functions/0506-exportToXls.php";
$fileName=date("Y-m-d")."_stock_ventes_".$order['myRange']."week.xls";
$str=exportToXls($fileName,$table,["inventaire"]);
echo "<a href='./files/$fileName'>$fileName</a>";

$stockNoSaleStr=exportToXls("StockNoSale_$fileName",$stockNoSale);
echo "<br><a href='./files/StockNoSale_$fileName'>StockNoSale_$fileName</a>";


echo $str;

echo $stockNoSaleStr;



?>

</body>
</html>

