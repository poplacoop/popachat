<?php
@session_start();
require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0505-miscellaneousFunctions.php";
include "../0021-functions/0501-retrieveFunctions.php";
include "0500-listFunctions.php";
include "0003-prepareData.php";

include "0501-graphFunctions.php";

include "0503-stockFunctions.php";


//----------------------------------------------------------------------
// Get Data
// All products
$listeProduitsQuery=$_REQUEST["query"];
$total="";
//$order=$_REQUEST['order'];

//echo $order['graph'];
$total=0;
//$filter=["ean","refFour","designation","colis"];

if (!isset($_SESSION['listeDesProduits'])){
    $_SESSION['listeDesProduits']='commande';} // in case not defined.
switch ($_SESSION['listeDesProduits']) {
    case 'commande':
        $filter=['ean','refFour','designation','conditionnement','uniteVente'];
        $special=['quantite','prixAchat','stock','editQuantite','search','ventes','erase','checkQuantite'];
        break;
    case 'stock':
        $filter=['ean','refFour','designation'];
        $special=['stock','ventes','classArticles'];
        break;
    case 'listeDesProduits':
        if ($_SESSION['tridisp']=="tridisp clicked"){
            $filter=['ean','refFour','tri','designation','conditionnement','uniteVente'];
        }
        else{
            $filter=['ean','refFour','designation','conditionnement','uniteVente'];
        }
        $special=['prixAchat','stock','ventes','quantite','editQuantite','checkQuantite']; 
        break;  
    case 'creationDeProduits':
        if ($_SESSION['tridisp']=="tridisp clicked"){
            $filter=['ean','refFour','tri','designation','conditionnement','uniteVente'];
        }
        else{
            $filter=['ean','refFour','designation','conditionnement','uniteVente'];
        }
        $special=[]; 
        break;      
        
}

if ($order['graphSales']=="on"){$special=array_merge($special,['graphSales']);}
if ($order['graphStock']=="on"){$special=array_merge($special,['graphStock']);}

echo createListe($listeProduitsQuery,'ean',$total,$filter,$special,$order);

?>
