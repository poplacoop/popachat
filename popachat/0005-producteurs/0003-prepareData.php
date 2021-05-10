<?php
if (isset($_REQUEST['logout'])){
    unset($_SESSION);
    header('Location:/index.php');
}
// reset fournisseur if requested
if (isset($_REQUEST['resetFournisseur'])){
    unset($_SESSION['fournisseur']);
    unset($_SESSION['commande']);
    unset($_SESSION['famille']);
    unset($_SESSION['departement']);
}

//var_dump($_SESSION['tridisp']);
//echo "<br>";
//var_dump($_REQUEST['tridisp']);
// treat tri disp: remains constant unless click on "tri"
// default is tri by command.
if (!isset($_SESSION['tridisp'])){
    $_SESSION['tridisp']=1;
} // if not tridisp not defined
if (isset($_REQUEST['tridisp'])){
    if ($_REQUEST['tridisp']==1){
        $_SESSION['tridisp']=0;
    }
    else{
        $_SESSION['tridisp']=1;
    }
}

// default cumul=0
if (!isset($_SESSION['nocumul'])){
    $_SESSION['nocumul']=1;
} // if not nocumul not defined
if (isset($_REQUEST['nocumul'])){
    if ($_REQUEST['nocumul']==1){
        $_SESSION['nocumul']=0;
    }
    else{
        $_SESSION['nocumul']=1;
    }
}




//if(!isset($_SESSION['graphNb'])){$_SESSION['graphNb']=1;}
//----------------------------------------------------------------------
// Treat Request
$command1=['groupe','fournisseur2','try','alf','uniteContenance','remarques','from','to','cc'];
$command2=['connexion','logout','reason','supprimerFamille','ean','select','departement','tri','marque'];

$commands=array_merge($command1,$command2);
$command3=['export','commande','nouvelleDate','new','duplicate','proteger','date_livraison','action'];
$commands=array_merge($commands,$command3);
$command4=['design','refFour','import','fileImport','upload','changeEan','insertProduct','validated'];
$commands=array_merge($commands,$command4);
$command5=['upload_pronadis_livraison','upload_csv','upload_xls','date_livraison_prevue','date_livraison_effective','date_traitement_facture','date_envoi'];
$commands=array_merge($commands,$command5);
$command6=['lock_commande','lock_livraison','lock_facture','upload_PLU','import_articles','tva'];
$commands=array_merge($commands,$command6);
$command6=['creationdepartement','creationfamille','creationfournisseur','eanSearch', 'refFourSearch','designationSearch','quantite','eanModif','myRange'];
$commands=array_merge($commands,$command6);
$command6=['designation','graphSales','graphStock'];
$commands=array_merge($commands,$command6);
$command6=['prixAchat','contenance','conditionnement','uniteVente','dlc','stock','progress','nocumul'];
$commands=array_merge($commands,$command6);
$order=array();

treat_request($order,$commands);




//----------------------------------------------------------------------
// Get Data

include "../0021-functions/0409-generateDico.php";

//----------------------------------------------------------------------
//echo "commande=".$order['commande'];
if ($order['commande']==""){
    if (isset($_SESSION['commande'])){
        $order['commande']=$_SESSION['commande'];  
    }
}
else{
    $_SESSION['commande']=$order['commande'];
}
if (!isset($_SESSION['commande'])){$_SESSION['commande']="";}
/*if($order['listefournisseur']!=""){
    $order['fournisseur']=$order['listefournisseur'];
}*/

//array allows to empty SESSION when REQUEST does not exist or is null string
//$empty=['departement'=>'departement','famille'=>'famille'];

$sessionVariables=['departement'=>'departement','famille'=>'famille','myRange'=>'myRange','fournisseur'=>'fournisseur'];
//var_dump($_SESSION);
//var_dump($_REQUEST);
foreach ($sessionVariables as $variable){
    //echo "<br>".$variable;
    // defines new $_SESSION if sumbitted
    if (isset($_REQUEST[$variable])){
        //echo "request defined";
        $_SESSION[$variable]=$_REQUEST[$variable];
        // erase commande if fournisseur has changed
        if($variable=="fournisseur"){
            if ($_SESSION[$variable]!=$_REQUEST[$variable]){
                    unset($_SESSION['commande']);
                    unset($_SESSION['famille']);
                    unset($_SESSION['departement']);
                    unset($order['commande']);
                    unset($order['famille']);
                    unset($order['departement']);
                    unset($order['myRange']);
                    unset($_SESSION['myRange']);
                    
                }
        }
        $order[$variable]=$_REQUEST[$variable];
    }
    // if no change submitted
    // if session not define, defines it as empty else keep it and create order
    else{
        //echo "request not defined";
        if (!isset($_SESSION[$variable])){ 
            $_SESSION[$variable]="";
        }
        $order[$variable]=$_SESSION[$variable];  
    }
    //echo "<br>$variable:".$order[$variable]."ses".$_SESSION[$variable];
}

//echo "myRange".$_SESSION['myRange'];
if ($_SESSION['myRange']==""){
    $order['myRange']=4;
    $_SESSION['myRange']=$order['myRange'];
}

/*
    if (($order[$variable]=="")&&(!in_array($variable,$empty))){
        echo "set";
        if (isset($_SESSION[$variable])){
            $order[$variable]=$_SESSION[$variable];  
        }
        else{
            $_SESSION[$variable]="";
        }
    }
    else{
        echo "unset";
        if (isset($_SESSION[$variable])){ 
            if($variable=="fournisseur"){
                if ($_SESSION[$variable]!=$order[$variable]){
                    unset($_SESSION['commande']);
                }
            }
            
        }
        else {
            $_SESSION[$variable]=$order[$variable];
        }
    }
}
*/
//var_dump($_SESSION);
//echo "s - r ";
//var_dump($_REQUEST);
//echo "fin prepare";

?>




