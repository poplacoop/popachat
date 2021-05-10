<?php
@session_start();

//var_dump($_SESSION);

include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0501-graphFunctions.php";

//----------------------------------------------------------------------
// Create the body of the page
//----------------------------------------------------------------------
//----------------------------------------------------------------------
// start html
//----------------------------------------------------------------------
// prepare menu

echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);


//var_dump($_REQUEST);
//echo $_SESSION['graph'];
//-----------------------------------------------------------------------
// Insert New Item in commandeList
$htmlNoInsert="";
// ajout d'une valeur
if ($order['ean']!=""){
	echo "ajout  d'une valeur";
	$tablekey=[];
	$tablekey['ean']=$order['ean'];
    $query="select * from prod_commandeList where commande_id=".$order['commande']." and ean='".$order['ean']."';";
    echo $query;
    $itemTable=query_table($query);
    //displayinhtml($itemTable);
    if (sizeof($itemTable)>1){
        $htmlNoInsert="<div>L'article ".$produitsDico[$tablekey['ean']]['designation']." est déjà dans la commande</div>"; 
    }
    else{
        //$tablekey['designation']=$_REQUEST['designation'];
        $tablekey['prixAchat']=$produitsDico[$tablekey['ean']]['prixAchat'];
        //$tablekey['quantite']=$_REQUEST['quantite'];
        $tablekey['commande_id']=$order['commande'];
        $filter=['ean','prixAchat','commande_id'];
        $query=create_INSERT('prod_commandeList',$tablekey,$filter,$order['commande']);
        //echo $query;
        simple_query($query);
    }
}
// modification d'une valeur
if ($order['eanModif']!=""){
	echo "modification d'une valeur";
	$tablekey=[];
	$tablekey['ean']=$order['eanModif'];
    $query="select * from prod_commandeList where commande_id=".$order['commande']." and ean='".$order['eanModif']."';";
    echo $query;
    $itemTable=query_table($query);
    //displayinhtml($itemTable);
    if (sizeof($itemTable)>1){
        echo "modification";
        if ($order['quantite']!=""){
            $query="update prod_commandeList SET quantite='".$order['quantite']."' where id='".$itemTable[1]['id']."';";
            echo $query;
            simple_query($query);
        }
        
        
    }
    else{
        
        $htmlNoInsert("problem with quantite modification");
    }
}

//----------------------------------------------------------------------
// Retrieve DATA
//----------------------------------------------------------------------
$cmdInfo="";
if ($order["commande"]!=""){
    $commandeQuery="SELECT * FROM prod_commande WHERE id='".$order["commande"]."' order by date_livraison_prevue";
    $commandeTable=query_table($commandeQuery);
    //var_dump($commandeTable);
    //displayTableInHtml($commandeTable);
    $cmdInfo=$commandeTable[1];
    $order['fournisseur']=$cmdInfo['fournisseur'];
    $_SESSION['fournisseur']=$order['fournisseur'];
    //var_dump($cmdInfo);
}

$where="";
if ($order['departement']!=""){
    $where=" and departement=".$order['listedepartement'];
}
if ($order['famille']!=""){
    $where.=" and famille=".$order['famille'];
}
if ($order['fournisseur']){
    $where.=" and fournisseur='".$order['fournisseur']."'";
}

$listeProduitsQuery="SELECT * FROM (SELECT * FROM prod_articles where validated<2) as prod_articles where 1 ".$where;
//echo $listeProduitsQuery;
$listeProduitsTable=query_table($listeProduitsQuery);
$produitDesignationDico=create_one_field_dictionnary($listeProduitsTable,"ean","designation");



$editArticleListe=['ean','refFour','designation','fournisseur','departement','famille','conditionnement','unite','contenance','tva','prixAchat','prixVente'];
    
// Update article
/*if (isset($_REQUEST['edit'])){
    //taken from function ListFullBis()
    //$attributeList=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat'];//,'p vente','stock','stock','fournisseur'];
    //var_dump($_REQUEST['edit']);
    $row=0;
    $nbAttribute=sizeof($editArticleListe);
    $nbRow=floor(sizeof($_REQUEST['edit']));
    while (isset($_REQUEST['editEan'][$row])){
        $ean=$_REQUEST['editEan'][$row];
        $edit=$_REQUEST['edit'];
        echo $ean."<br>";
        for ($i=1;$i<$nbAttribute;$i++){
            if (isset($edit[$i-1+$row*($nbAttribute-1)])){
                //echo $i.$editArticleListe[$i];
                //echo $edit[$i-1+$row*$nbAttribute];
                $query="update prod_articles set ".$editArticleListe[$i]."='".$edit[$i-1+$row*($nbAttribute-1)]."' where ean='".$ean."';";
            
                //echo $query."<br>";
                simple_query($query);
            }

        }
        $row++;
    }
    //header("Location:0-generalHeader.php?fournisseur=".$order['fournisseur']."&commande=".$order['commande']);
}
*/
$insertMsg="";

// Liste des produits de la commande
$htmlList= "<div id='searchList'>";
//include "408-produitEditList.php";

//$htmlList.=$str;
$htmlList.= "</div>";

//----------------------------------------------------------------------
// get information from selected commande
if($order['commande']!=""){
    $commandeQuery="SELECT ean,quantite from prod_commandeList WHERE commande_id=".$order['commande'];
    //echo $commandeQuery."<br>";
    //$query="select list.*,cmd.quantite from ($listeProduitsQuery) as list left outer join ($commandeQuery) as cmd on list.ean=cmd.ean";
    //echo $query."<br>";
    $commandeTable=query_table($commandeQuery);
    $commandeDico=create_one_field_dictionnary($commandeTable,"ean","quantite");
    $nb=sizeof($commandeTable);
    
}
else{
     $query=$listeProduitsQuery;  
}




//----------------------------------------------------------------------
// title and selected fournisseur and items
//echo "graph=".$order['graph'];
echo "<h1>$titre</h1>";
if ($order['fournisseur']){
    echo "<div class='chosen'>".$order['fournisseur']."-".$dico['fournisseur'][$order['fournisseur']]."</div>";
    echo "<input type='hidden' name='fournisseur', id='fournisseur' value='".$order['fournisseur']."'></input>";
}
else{
    echo "<div class='chosen '>Fournisseur non sélectionné </div>";
    echo "<input type='hidden' name='fournisseur', id='fournisseur' value=''></input>";
    $selectedId="";
}
if ($order['commande']){
    $selectedId=$order['commande'];
    echo "<div class='chosen'>no ".$order['commande']." pour le ".$cmdInfo['date_livraison_prevue']." avec $nb articles</div>";
    echo "<input type='hidden' name='commande', id='commande' value='".$order['commande']."'></input>";
}
else{
    echo "<div class='chosen'> Commande non sélectionnée</div>";
    echo "<input type='hidden' name='commande', id='commande' value=''></input>";
    $selectedId="";
}
echo $htmlNoInsert;

//----------------------------------------------------------------------
// begin form
$str="<form name='myForm' method='post'>";
//----------------------------------------------------------------------
// range for history and graph
$str.= "<div class='theRange'>";
$str.=" <span class='text' >Somme des ventes sur </span>";

if ($order['graphSales']=="on"){$checked="checked";}else{$checked="";}

$str.= "<input name='myRange' id='inputRange' onchange='submit();'  value='".$order['myRange']."'></input><span id='text2'>semaines.</span>";
$str.= "<input type='range' class='form-range'  min='0' max='12' value='".$order['myRange']."' step='1' name='myRangeBar' id='myRangeBar'></input>";

$str.= "</div>";

// graphiques
$str.= "<div class='theRange'>
             
             <div> Voulez-vous un graphique de l'historique?</div>";
            $str.="
            <div class='form-check form-switch'>
              <input class='form-check-input' type='checkbox' id='flexSwitchCheckDefault' name='graphSales' $checked onchange='submit();'/>
            </div>
</div>";


// search items
$filterList=['departement','famille'];

foreach ($filterList as $item){
    if ($order['fournisseur']){
        $where.=" and fournisseur=".$order['fournisseur'];
    }
    $query="SELECT $item from prod_articles where validated<2 $where";
    $query="SELECT * from ($query) as a group by $item";
    $query="SELECT * from ($query) as sel left outer join prod_$item as list on sel.$item=list.id";
    $itemTable=query_table($query);
    $filter[$item]=create_one_field_dictionnary($itemTable,"id","titre");
    //displayinhtml($itemTable);
    //echo "item=".$item." ";
    $str.= listeSelect($filter,$item,"liste".$item,$order[$item],1); // the 1 if for order
}

if (!$order['fournisseur']){
    $str.= listeSelect($dico,"fournisseur","fournisseur",$order['fournisseur'],1); //0 is for no empty line added.
}
//var_dump($_REQUEST);
//echo "graph=".$order['graph'];
//echo "<script>
//graph=$(\"input[name='graph']\").val();
//        console.log(\"graphRun=\"+graph);
//</script>";
//----------------------------------------------------------------------
// create liste
$total=0;
//$filter=["ean","refFour","designation","colis"];
//$filter=['ean','refFour','designation','conditionnement'];
//$special=['quantite','prixAchat','stock','erase','editQuantite','search','ventes'];
//if ($order['graph']!=""){$special=array_merge($special,['graph']);}
        $str.="<table>
        <tr><th>ean</th><th>refFour</th><th>designation</th></tr>
        <tr id='search' >
            <td><input id='eanSearch' name='eanSearch' value='".$order['eanSearch']."' ></input></td>
            <td><input id='refFourSearch' name='refFourSearch' value='".$order['refFourSearch']."'></input></td>
            <td><input id='designationSearch' name='designationSearch' value='".$order['designationSearch']."'></input></td>
            <td><a id='exportXls' href='./0017-stockExcel.php' onclick='exportXls();' target='_blank'>ExportExcel</a></td>
        </tr>
        </table>"; 
$str.="<div id='theGraph'></div>";
$str.="<div id='itemsList'>";
//$str.=createListe($listeProduitsQuery,'ean',$total,$filter,$special,$order);

echo "</div>";
$str.="</form>";
echo $str;


echo "</div>";
//var_dump($order);
?>




