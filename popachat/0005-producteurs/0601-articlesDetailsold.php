<?php
@session_start();

//var_dump($_SESSION);
//var_dump($_REQUEST);

include "0000-initFilesProd.php";
include "0003-prepareData.php";
include "0500-listFunctions.php";
//include "0501bis-graphFunctions.php";
include "0503-stockFunctions.php";

$ean=$order['ean'];
// retrieve pop inventaire
$query="SELECT * FROM prod_stock_mvt where bl='POPinventaire' and ean=".$order['ean'];
$table=query_table($query,1);
$popInventaireDico=create_product_dictionnary($table,'id');
//dispArray($popInventaireDico[11014]);
//----------------------------------------------------------------------
// modify stocks
if (isset($_REQUEST['dateStock'])){
    if ($_REQUEST['dateStock']!=""){
        $query="INSERT into prod_stock_mvt (thedate,bl,ean,ajout,raison,source,user) VALUES 
        ('".$_REQUEST['dateStock']."','POPinventaire','".$order['ean']."','".$_REQUEST['newStock']."','INVENTAIRE','SITE','".$_SESSION['userInfo']['userId']."')";
        //echo $query;
        simple_query($query);
        header("Location:0601-articlesDetails.php?ean=$ean");
    }
    $edit=$_REQUEST['edit'];
    foreach ($edit as $key=>$val){
        if ($val[0]!=$popInventaireDico[$key]['ajout']){
            if ($val[0]==""){
                $query="DELETE FROM prod_stock_mvt WHERE id=".$key;
            }
            else{
                $query="UPDATE prod_stock_mvt SET ajout=".$val[0]." WHERE id=".$key;
            }
            //echo $query;
            simple_query($query);
        }
        //echo "<br>key=".$key."<br>";
        //echo $val[0];
        //echo $popInventaireDico[$key]['ajout'];
        //if ($row
        
    } 
}

// retrieve pop inventaire
$query="SELECT * FROM prod_stock_mvt where bl='POPinventaire' and ean=".$order['ean'];
$table=query_table($query,1);

//include "0501new-graphFunctions.php";
//----------------------------------------------------------------------
// find next article
$where="";
if (isset($_SESSION['fournisseur'])){
    $where=" and fournisseur=".$_SESSION['fournisseur'];
}
$query="SELECT * FROM prod_articles where validated<2 $where order by tri";
$tableDico=query_table_dico($query);
//var_dump($tableDico);
$nb=0;
 
foreach($tableDico as $row){
    
    if($row['ean']==$order["ean"]){break;}
    $nb++;
}
$nextEan=$order['ean'];
$prevEan=$order['ean'];
//echo "$nb";
//echo sizeof($tableDico);
if($nb+1<sizeof($tableDico)){
    $nextEan=$tableDico[$nb+1]['ean'];
}
if ($nb>0){
    $prevEan=$tableDico[$nb-1]['ean'];
}
//dispArray( $tableDico[$nb]);
//dispArray( $tableDico[$nb+1]);
if (($nb+1<sizeof($tableDico))&&(!isset($_REQUEST['newean']))){
    
}

$stockDico=getstockInformationDico($order['myRange'],$order['ean']);

//var_dump($stockDico);
//----------------------------------------------------------------------
// Create the body of the page
//----------------------------------------------------------------------
//----------------------------------------------------------------------
// start html
//----------------------------------------------------------------------
// prepare menu
echo myheader();
echo "<body>";
// retrieve details of article.
$query="select * from prod_articles where ean=".$order["ean"];
$tableArticle=query_table($query);
$designation=$tableArticle[1]['designation'];
$ean=$tableArticle[1]['ean'];
//----------------------------------------------------------------------
// retrieve last stock for article
$query="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
        FROM `prod_stock` where not isnull(stock) and ean=".$order["ean"]." group by ean) as last 
        left outer join (SELECT * FROM prod_stock where ean=".$order["ean"].") as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
        where last.ean=".$order["ean"]." ORDER BY `last`.`thedate` ASC ";
//echo $query;
$table=query_table($query);
//displayinhtml($table);
if (sizeof($table)>1){
    $stock=$table[1]['stock'];
    $stockDateTbl=$table[1]['thedate'];
    $isStock=1;
}
else{
    $isStock=0;
}
$stock=0;
if (isset($stockDico[$row['ean']])){
    $stock=$stockDico[$row['ean']]['stock'];
}

//----------------------------------------------------------------------
// Evaluate rupture de stock
include "0604-rupture.php";

$ruptureStock=evaluateRupture($order,$ean);
$ruptureDaysStr="";
if (isset($ruptureStock[$ean])){
    if ($ruptureStock[$ean]['nombreDeJours']>0){
        $ruptureDaysStr="<div id='rupture'>Il y a eu ".$ruptureStock[$ean]['nombreDeJours']." jours de rupture de stock durant les ".$order['myRange']." semaines<br>";
        //$ruptureDaysStr.="Cela correspond à une absence de bénéfice d'environ ".mynumber_format($ruptureStock[$ean]['manqueAGagner'],0)." euros";
        $ruptureDaysStr.="</div>";
    }
}
//----------------------------------------------------------------------
// Beginning of html
//var_dump($_SESSION);
echo "<h1>$designation</h1>
<p>".$ean."</p>
<form>
<button value='".$prevEan."' name='ean'>PRECEDENT</button>
<button id='retour' value='retour'>RETOUR</button>
<button value='$nextEan' name='ean'>SUIVANT</button>
</form>";
$uniteVente=$tableArticle[1]['uniteVente'];
if ($uniteVente==1){$unit="unité";}else{$unit=$tableArticle[1]['uniteVente'];}



// Détermination du temps d'observation
// le curseur du temps
$buttonStr= "<form name='myForm'>";
$buttonStr.="<div class='theRange'>";
$buttonStr.=" <span class='text' >Somme des ventes sur </span>";

if ($order['graphSales']=="on"){$checked="checked";}else{$checked="";}

$buttonStr.= "<input id='myRange' name='myRange' value='".$order['myRange']."'></input>";
$buttonStr.= "<div class='text'>semaines.</div>";
$buttonStr.= "<input type='range' class='form-range' min='0' max='12' value='".$order['myRange']."' step='1' id='myRangeBar'>";
$buttonStr.="</div>
<input type='hidden' name='ean' value='".$order['ean']."'></input>";
$buttonStr.="</form>";

// Détermination de la dernière date de vente
$query="select max(thedate) as lastdate from (SELECT ean,max(thedate)as thedate FROM `prod_plu` group by ean) as a";
$table=query_table($query);
$lastdate=$table[1]['lastdate'];


// Détermination des Ventes total
$unitString=['U'=>'unité','kg'=>'kg','1'=>'unité'];
$query="Select ean,sum(quantite) as ventes from (SELECT * FROM `prod_plu` WHERE thedate > current_date- interval ".$order['myRange']." week and ean=".$order['ean'].") as a group by ean";
$table=query_table($query);
//displayinhtml($table);
//echo $uniteVente;
//echo $stockDateTbl;
$stockDate="";
$stockStr="";
if($isStock){  
    $stockDate=convert_date($stockDateTbl);
    $stockStr=mynumber_format($stock,2)." ".$unit;
}

$salesStr="0";
$salesDate="";
$stockDuration=0;
if (sizeof($table)>1){
    $sales=$table[1]['ventes'];
    if ($sales>1){$s='s';}else{$s='';}
        $salesStr=mynumber_format($sales,2)." ".$unit;
        $salesDate=convert_date($lastdate);
    if ($stock>0){
        $stockDuration=$stock/$sales*$order['myRange'];
    }
}

$commandeDurationStr="0";
$commandeQuantiteStr="0";
$commandeStockDurationStr="0";
$strRecommandation="";
// Commande en cours
if($order['commande']!=""){
    $commandeQuery="SELECT ean,quantite from prod_commandeList WHERE commande_id=".$order['commande'];
    //echo $commandeQuery."<br>";
    //$query="select list.*,cmd.quantite from ($listeProduitsQuery) as list left outer join ($commandeQuery) as cmd on list.ean=cmd.ean";
    //echo $commandeQuery."<br>";
    $commandeTable=query_table($commandeQuery);
    $commandeDico=create_one_field_dictionnary($commandeTable,"ean","quantite");
    $nb=sizeof($commandeDico)-1;
    // Compute duration of commande
    if (isset($sales)){
        if (($sales>0) && isset($commandeDico[$order['ean']])){
            
            $salesDuration=$commandeDico[$order['ean']]/$sales*$order['myRange'];
            $commande1mois=(floor(($stockDico[$row['ean']]['commande1mois']-0.0001)/$row['conditionnement'])+1)*$row['conditionnement'];
            $commandeQuantiteStr=mynumber_format($commandeDico[$order['ean']],2)." ".$unit;
            $commandeDurationStr=mynumber_format($commandeDico[$order['ean']]/$sales*$order['myRange'],2);
            $commandeStockDurationStr=mynumber_format($salesDuration+$stockDuration,2);
            $class='pasassez';
            $msg="<h3>On conseille $commande1mois ".$unitString[$uniteVente]."<br>";
            
            if ($commandeDico[$order['ean']]>$commande1mois){
                $class='trop';
                $msg="<h3>On conseille $commande1mois ".$unitString[$uniteVente]."</h3>";
            }
            if ($commandeDico[$order['ean']]==$commande1mois){
                $class='ok';
                $msg="<h3>Les quantités semblent correctes!</h3>";
            }
            $strRecommandation="<div class='recommandation $class'>$msg";
            $strRecommandation.="cette recommandation n'est pas valable si le produit a été en rupture de stock ou est nouveau";
            $strRecommandation.="</div>";
            //echo "<h3>La commande de ". représente ".mynumber_format($salesDuration,2)." semaines.</h3>";
            //echo "<h3>La commande proposée et le stock représente ".mynumber_format($salesDuration+$stockDuration,2)." semaines </h3>";
        }
    }
}

//----------------------------------------------------------------------
// get information from plu for all dates
//----------------------------------------------------------------------
$maxDay=$order['myRange']*7; // defines depth to look for
$sql="SELECT thedate,floor(DATEDIFF(NOW(),thedate)/(".$order['myRange']."*7)) as period,quantite
FROM prod_plu WHERE ean=".$order['ean']."  order by thedate";
$query="SELECT min(thedate) as thedate, period,sum(quantite) as quantite FROM ($sql) as A group by period order by period asc limit 5";
$query="SELECT * FROM ($query) as A order by period desc";
//echo $sql;
$table=query_table($query,1);
//displayinhtml($table);
array_shift($table);
$strPlusPrevious="Dernières ventes par période de ".$order['myRange']." semaines (la date correspond au début de la période)";
$strPlusPrevious.="<table class='salesHistory'>"; 
$headStr="<tr>";
$salesHistStr="<tr>";

foreach ($table as $row){
    $headStr.= "<td>".convert_date($row['thedate'])."</td>";
    $salesHistStr.= "<td>".mynumber_format($row['quantite'],2)."</td>";
}

$headStr.="</tr>";
$salesHistStr.="</tr>";
$strPlusPrevious.=$headStr;
$strPlusPrevious.=$salesHistStr;
$strPlusPrevious.="</table><p></p>";

// Details
//echo "coucou";
$stockVenteStr= "<table class='detail'>
<tr><th></th><th>Date</th><th>Quantité</th><th>Durée (semaines)</th></tr>";
//echo "<tr><td>Les Ventes</td><td>".convert_date($lastdate)."</td><td>$salesStr</td><td>".$order['myRange']."</td></tr>
$stockVenteStr.="<tr><td>Les Ventes</td><td>".convert_date($lastdate)."</td><td>$salesStr</td><td>".$order['myRange']."</td></tr>";
$stockVenteStr.= "<tr><td>Le stock</td><td>".$stockDate."</td><td>$stockStr</td><td>".mynumber_format($stockDuration,1)."</td></tr>";
$stockVenteStr.="</table>";
//echo "coucou";
if($order['commande']!=""){
    if (isset($commandeDico[$order['ean']])){
        $stockVenteStr.="
        <br>
        <table class='detail'>
        <tr><th></th><th>Quantité</th><th>Durée (semaines)</th></tr>
        <tr><td>La commande</td><td >$commandeQuantiteStr</td><td>$commandeDurationStr</td></tr>
        <tr><td>Stock+Commande</td><td class=$class >$commandeQuantiteStr</td><td>$commandeStockDurationStr</td></tr>
        </table>";
    }
}
echo $buttonStr;

echo $strPlusPrevious;
echo $ruptureDaysStr;
echo $stockVenteStr;
echo $strRecommandation;

//----------------------------------------------------------------------
// Display Stocks
//$data=getStockValue($order);

//echo graphStockNew($data,$designation);
//echo plot_chart_stocks_new($order["ean"],$designation,$order);

echo "Les barres bleues sont les ventes, les barres vertes les livraisons, les symboles rouges les inventaires.";
//----------------------------------------------------------------------
// Evaluate sales for the given myrange
// look for sales for duration


//----------------------------------------------------------------------


//include "0501new-graphFunctions.php";
//$order['ean']='2000000000145';
$data=getStockValue($order,$order['ean']);
//dispArray($data['sales']);
$str=graphStockNew($data,$designation);
echo $str;

if ($_SESSION['userInfo']['admin']){
    echo "<form name='stockForm'>
    Ajustement des stocks<input type='date' name='dateStock'></input>
    <input name='newStock'></input>
    <input name='ean' value='".$order['ean']."' type='hidden'></input>
    <button type='submit' name='stockAdjust'>Enregistrer</button>";
    
    
    echo "<table inventaire>";
    foreach ($popInventaireDico as $row){
        echo "<tr>
            <td>".$row['thedate']."</td>
            <td><input name=edit[".$row['id']."][] value=".$row['ajout']."></input></td>
        </tr>";
    }
    echo "</table>";
    echo "</form>";
        //displayinhtml($table);
}
?>

<script>

$('#myRangeBar').change(function() {
        console.log($(this).val());
        $('[name="myRange"]').val($(this).val());
        $('[name="myForm"]').submit();
    });
    $('[name="myRange"]').change(function() {
        console.log($(this).val());
        $('[name="myRangeBar"]').val($(this).val());
        $('[name="myForm"]').submit();
    });
$('#retour').click(function(){
    window.close();
});
</script>
</body>
</html>

