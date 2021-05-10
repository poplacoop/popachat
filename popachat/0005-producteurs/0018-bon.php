<?php
@session_start();

include "0000-initFilesProd.php";
include "0500-listFunctions.php";


$commandeId=$_SESSION['commande'];
$displayTotal=1;
//echo $commandeId;
// get list of items
//var_dump($_SESSION['tridisp']);
if ($_SESSION['tridisp']==1){$queryTri=" order by ARTI.tri";}else{$queryTri=" order by ARTI.designation";}
$commandQuery="SELECT LIST.*,ARTI.designation,ARTI.refFour,ARTI.departement, ARTI.famille, ARTI.fournisseur, ARTI.tva,ARTI.conditionnement,ARTI.contenance, ARTI.uniteContenance, ARTI.uniteVente FROM
               (SELECT * FROM prod_commandeList WHERE commande_id=$commandeId order by ean) AS LIST         
               LEFT OUTER JOIN 
               (SELECT * FROM (SELECT * FROM prod_articles where validated<2 order by ean) as prod_articles ) AS ARTI
               ON LIST.ean=ARTI.ean $queryTri ";

$commandeListTable=query_table($commandQuery);
//----------------------------------------------------------------------
//  Retrieve options
//
$liste=['refFour'=>'Réf.','colis'=>'Colis','conditionnement'=>'Cond.',
'quantite'=>'Quantité','prixAchat'=>'Prix','montant'=>'Montant',];
$listeDefault=['refFour'=>1,'colis'=>1,'conditionnement'=>0,'quantite'=>1,'prixAchat'=>1,'montant'=>1];
$print=[];
// change $listeDefault if valider submitted
if (isset($_REQUEST['valider'])){
    //echo "valider";
    foreach ($liste as $key=>$val){
        if (isset($_REQUEST[$key])){
            //array_push($print,$key);
            $listeDefault[$key]=1;
        }
        else{
            $listeDefault[$key]=0;
        }
    }
}
//var_dump($listeDefault);

// correct to make sure there is on of quantite ou colis
if (($listeDefault['quantite']==0)&&($listeDefault['colis']==0)){
    array_push($print,"colis");
    $listeDefault['colis']=1;
}
// create $input
//echo "filter=";
//var_dump(array_keys(array_filter($listeDefault)));
//echo "<br>";

// filter values of $listeDefault with "1" then extract the key
$print=array_keys(array_filter($listeDefault));

//dispArray($print);
//echo $_REQUEST['valider'];

//---------------------------------------------------------------------
// retrieve command data and make output file name
//
$query="SELECT * FROM prod_commande WHERE id='$commandeId'";
$chosenCommandeTable=query_table($query);
//displayinhtml($chosenCommandeTable);
$field=[];
for ($k=0;$k<sizeof($chosenCommandeTable[0]);$k++){
    $field[$chosenCommandeTable[0][$k]]=$chosenCommandeTable[1][$k];
}

//
// definition du nom de fichier
$fourName=$dico['fournisseur'][$_SESSION['fournisseur']];
$outputFileNameRoot="$fourName-no".$field['id']."-".$field['date_livraison_prevue'];
$fullOutputFileNameRoot = './files/'.$outputFileNameRoot;

// creation du pdf
include "0600-creationBonDeLivraisonPdf.php";
//echo "coucouAfterpdf";
// creation excel
// retrieve filename
$sql="SELECT formatXLS,fichierimport from prod_fournisseur WHERE id='".$order['fournisseur']."';";
$table=query_table($sql);
//----------------------------------------------------------------------
// fichier import fournisseur
//echo "coucou Before Excel";
$fichierCommandeFournisseur=$table[1]['fichierimport'];
 
if ($fichierCommandeFournisseur==""){
    //echo  "0605-init";
    include "0605-creationBonDeLivraisonXls.php";
    $htmlFichierFounisseur="";
    //$str.= "0605";
    $str="";
}
else{
//if ($_SESSION['userInfo']['admin']){
        // generate excel output if defined.
        //echo "0512-init";
        include "0512-export_excel_commande.php";
        $htmlFichierFounisseur= "Fichier fournisseur:".$fichierCommandeFournisseur."<br>";
        //echo "0512";
//}
}
//$str.= "coucou After Excel";
include "../0021-functions/600-sendmail.php";

//echo $outputFilename;
//var_dump($_SESSION);
/*
use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../vendor/phpmailer/phpmailer/src/Exception.php';
    require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require '../vendor/phpmailer/phpmailer/src/SMTP.php';

*/
  
//----------------------------------------------------------------------
//if (isset($_REQUEST['destinataire'])){
$envoieMessage="";
if ((isset($_REQUEST['subject']))&&(isset($_REQUEST['sendMail']))){
    //var_dump($_REQUEST['omitXls']);
    $subject=$_REQUEST['subject'];
    $content=$_REQUEST['content'];
    $to=$order['to'];
    
    $from=$order['from'];
    $cc=$order['cc'];
    //$str.= $cc;
    $bcc="";
    
    //echo "to".$to."<br>";
    //echo "from".$from."<br>";
    //echo "cc".$cc."<br>";
    //echo "cc".$cc."<br>";
    //$mail = new PHPMailer();
    $str.="Les fichiers envoyés sont:<br>";
    if (!isset($_REQUEST['omitXls'])){
        $str.= $fullOutputFileName.".pdf<br>";
        $attach=[[$fullOutputFileNameRoot.".pdf",$outputFileNameRoot.".pdf"]];
    }
    else{
        $str.= $fullOutputFileName.".pdf et <br>".$fullOutputFileName.".xls<br>";
        $attach=[[$fullOutputFileNameRoot.".xls",$outputFileNameRoot.".xls"],[$fullOutputFileNameRoot.".pdf",$outputFileNameRoot.".pdf"]];
    }
    
    
    if(!sendMail($from,$to,$cc,$bcc,$subject,$content,$attach)) {
      $str.= "<br>Error while sending Email.";
      var_dump($mail);
    } 
    else {
      $str.= "<br>La commande a été envoyée avec succès";
      $sql="UPDATE prod_commande SET destinatairesCommande='".htmlentities($to)."',contenuMessageMail='".htmlentities($content)."' WHERE id='".$_SESSION['commande']."';";
      simple_query($sql);
    }
    $envoieMessage=$str;
}

//----------------------------------------------------------------------
// prepare menu
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);

//shell_exec("rm ./files/bonDeCommande.jpg");
//shell_exec("rm ./files/bonDeCommande-0.jpg");
//shell_exec("rm ./files/bonDeCommande-1.jpg");

// create bon
//$pdf_file = escapeshellarg( "./files/bonDeCommande.pdf" );
//$jpg_file = escapeshellarg( "./files/bonDeCommande.jpg" );

$result = 0;
//echo "convert -density 300 ".$pdf_file." ".$jpg_file;
//exec( "convert -density 300 ".$pdf_file." ".$jpg_file);

//---------------------------------------------------------------------
// retrieve command data and make output file name
$query="SELECT * FROM prod_commande WHERE id='".$_SESSION['commande']."'";
$chosenCommandeTable=query_table($query);
//displayinhtml($chosenCommandeTable);
$chosenCommandeDico=[];
for ($k=0;$k<sizeof($chosenCommandeTable[0]);$k++){
    $chosenCommandeDico[$chosenCommandeTable[0][$k]]=$chosenCommandeTable[1][$k];
}



//$outputFilename="no".$chosenCommandeDico['id']."-".$field['date_livraison_prevue'];
//echo "bonoutputfilename".$outputFilename;
$query="SELECT * FROM prod_fournisseur where id=".$_SESSION['fournisseur'];
$dicoFournisseur=query_table_dico($query);

// Récupérer les referents pop
if ($dicoFournisseur[0]['referentPop2']!=""){
    $query="SELECT * FROM prod_user where id=".$dicoFournisseur[0]['referentPop']." OR id=".$dicoFournisseur[0]['referentPop2'];
}
else{
    $query="SELECT * FROM prod_user where id=".$dicoFournisseur[0]['referentPop'];
}
$dicoUser=query_table_dico($query);
$referentPop1=$dicoUser[0]['email'];
$referentPop2="";
if (isset($dicoUser[1]['email'])){$refPop2=$dicoUser[1]['email'];}
//

//var_dump($dicoUser);
$requestTitle=['from','to','cc'];
$mailParam=[];
$mailParam['from']=$_SESSION['userInfo']['email'];

$mailParam['to']=$dicoFournisseur[0]['email'];
$mailParam['cc']=$_SESSION['userInfo']['email'].",producteurs@poplacoop.fr";
$comma=",";

// liste des destinataires


if (($referentPop1!="")&&($referentPop1!=$_SESSION['userInfo']['email'])){
    $mailParam['cc'].=$comma.$referentPop1;
}
if (($referentPop2!="")&&($referentPop2!=$_SESSION['userInfo']['email'])){
    $mailParam['cc'].=$comma.$referentPop2;
}

foreach ($requestTitle as $name){
    //echo $name." ".$order[$name]."<br>";
    if ($order[$name]!=""){
        $mailParam[$name]=$order[$name];
    }
}

//----------------------------------------------------------------------
// début du document html
//----------------------------------------------------------------------
?>

<!DOCTYPE html>



<html>
  <head>
    <title>Bon de Commande</title>
  </head>
  <body>
    <h1>Bon de Commande</h1>
    <?php
    //echo $str;

    if ($order['fournisseur']){
        echo "<div class='chosen'>".$order['fournisseur']."-".$dico['fournisseur'][$order['fournisseur']]."</div>";
        echo "<input type='hidden' name='fournisseur', id='fournisseur' value='".$order['fournisseur']."'></input>";
    }
    if ($order['commande']){
        $selectedId=$order['commande'];
        $commandeQuery="SELECT * FROM prod_commande WHERE id='".$order["commande"]."' order by date_livraison_prevue";
        $commandesTable=query_table($commandeQuery);
        //var_dump($commandeTable);
        //displayTableInHtml($commandeTable);
        $cmdInfo=$commandesTable[1];
        $commandeQuery="SELECT ean,quantite from prod_commandeList WHERE commande_id=".$order['commande'];
        //echo $commandeQuery."<br>";
        //$query="select list.*,cmd.quantite from ($listeProduitsQuery) as list left outer join ($commandeQuery) as cmd on list.ean=cmd.ean";
        //echo $query."<br>";
        $commandeTable=query_table($commandeQuery);
        $commandeDico=create_one_field_dictionnary($commandeTable,"ean","quantite");
        $nb=sizeof($commandeTable);
        echo "<div class='chosen'>no ".$order['commande']." pour le ".$cmdInfo['date_livraison_prevue']." avec $nb articles</div>";
        echo "<input type='hidden' name='commande', id='commande' value='".$order['commande']."'></input>";
    }
    echo $htmlFichierFounisseur."<br>";
    
    echo $envoieMessage;

    echo "Choix de colonnes pour les bons de commande
    <form id='myForm'>
    <table id='choixColonne'>";
    $headStr="<tr>";
    $bodyStr="<tr>";
    foreach ($liste as $key=>$val){
        $headStr.="<td>$val</td>";
        
        $checked=$listeDefault[$key]==1?"checked":"";
        //echo $key."=".$listeDefault[$key]."---";
        $bodyStr.="<td><input type='checkBox' $checked name='$key'></input></td>";
    }
    $headStr.="</tr>";
    $bodyStr.="<td><button name='valider'>valider</button></td></tr>";
    echo $headStr;
    echo $bodyStr;
    
    
    echo "</table>";
    
    
    echo "<p><a href='$fullOutputFileNameRoot.pdf'>$outputFileNameRoot.pdf</a></p>";
    $checked=(isset($_REQUEST['omitXls']))?"checked":"";
    echo "<p><a href='$fullOutputFileNameRoot.xls'>$outputFileNameRoot.xls</a><input type='checkBox' id='omitXls' name='omitXls' $checked ></button></p>";
    //echo "</form>";


    //echo "<iframe src='files/bon-".$outputFilename.".pdf' width='100%' height='500px'/>";
    ?>
    <!--</iframe>
    <img src='./files/bon-".$outputFilename."-0.jpg' width="600px"/>
    <br>
    <img src='./files/bonDeCommande-0.jpg' width="600px"/>
    <br>
    <img src='./files/bonDeCommande-1.jpg' width="600px"/>*/
    -->
    
<?php


//$emailFournisseur='didier.cransac@free.fr';
echo "<p>Souhaitez-vous envoyer le bon de commande?</p>";
//echo "<form id='myForm'>";
echo "<p>De:<input name='from' value=".$mailParam['from']."></input></p>";
echo "<p>A:<input name='to' value=".$mailParam['to']."></input></p>";
echo "<p>Copie:<textarea name='cc' cols='80'>".$mailParam['cc']."</textarea></p>";

echo "<p>Sujet<input name='subject' value='BON DE COMMANDE POP LA COOP NUM. ".$outputFileNameRoot ."'></input></p>";
echo "<p>Contenu<textarea rows=5 cols='80' name='content' >Veuillez trouver ci-joint (fichier pdf attach&eacute;) notre bon de commande no la commande pour le bon $outputFileNameRoot</textarea></p>";

echo "<button id='sendmail' name='sendMail'>Envoyer le mail de commande</button>";
echo "</form>";
 ?>   
    
    
    
<script>
    $(".leftNav").find("div").eq(4).addClass('navSelected'); // select menu "bon"
    $('#help').click(function(){
        console.log( 'help clicked ' );
        $(this).attr("href", "documentation/manuel.php#bon");
    });
    $('#sendmail').click(function(){
        if (confirm("Voulez vous vraiment envoyer la commande?")){
            console.log("form submitted");
            return true;
            //$("#myForm").submit();
            
        }
        else{
            console.log("form not submitted");
            return false;
        }
        
    }
    );

</script>
    
  </body>
</html>
