<?php
if (isset($_REQUEST['logout'])){
    unset($_SESSION);
    header('Location:/index.php');
}

//if (isset($_REQUEST['reset'])){
//    unset($_REQUEST['select']);
//}

//if(!isset($_SESSION['graphNb'])){$_SESSION['graphNb']=1;}
//----------------------------------------------------------------------
// Treat Request
$command1=['famille','fournisseur','departement','try','alf'];
$command2=['menu'];
$commands=array_merge($command1,$command2);
$command3=['export','commande','nouvelleDate','new','duplicate','proteger','date_livraison'];
$commands=array_merge($commands,$command3);
$command4=['design','refFour','import','fileImport','upload','changeEan','insertProduct'];
$commands=array_merge($commands,$command4);
$command5=['upload_pronadis_livraison','upload_csv','upload_xls','date_livraison_effective','date_traitement_facture','date_envoi'];
$commands=array_merge($commands,$command5);
$command6=['lock_commande','lock_livraison','lock_facture','upload_PLU','import_articles','tva','historique'];
$commands=array_merge($commands,$command6);
$command6=['listedepartement','listefamille','eanSearch', 'refFourSearch','designationSearch','quantite','eanModif','myRange','historique'];
$commands=array_merge($commands,$command6);
$order=array();

treat_request($order,$commands);
if ($order['myRange']==""){$order['myRange']=4;$_SESSION['myRange']=$order['myRange'];}
//----------------------------------------------------------------------
// Get Data
include "../0021-functions/0409-generateDico.php";
//----------------------------------------------------------------------
if ($order['commande']==""){
    if (isset($_SESSION['commande'])){
        $order['commande']=$_SESSION['commande'];  
    }
}
else{
    $_SESSION['commande']=$order['commande'];
}



$sessionVariables=['fournisseur','myRange'];

foreach ($sessionVariables as $variable){
    if ($order[$variable]==""){
        if (isset($_SESSION[$variable])){
            $order[$variable]=$_SESSION[$variable];  
        }
    }
    else{
        $_SESSION[$variable]=$order[$variable];
        if($variable=="fournisseur"){unset($_SESSION['commande']);}
    }
}

//var_dump($_SESSION);
//echo "s - r ";
//var_dump($_REQUEST);
//echo "fin prepare";

?>




