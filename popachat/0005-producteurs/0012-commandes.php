<?php
@session_start();
if (isset($_REQUEST['commande'])){
    $_SESSION['departement']="";
    $_SESSION['famille']="";
    
    
    header("Location:0013-liste.php");
}
//var_dump($_SESSION);
//var_dump($_REQUEST['commande']);

// prepare to find back fournisseur if not defined.
$fac=0;

$where="";
if ($fac==0){$where=" and progress!=3";}

include "0000-initFilesProd.php"; // init files
//var_dump($order['commande']);

$cmdInfo="";
if ($order["commande"]!=""){
    $commandeQuery="SELECT * FROM prod_commande 
        WHERE id='".$order["commande"]."' order by date_livraison_prevue";
    $commandeTable=query_table($commandeQuery);
    //var_dump($commandeTable);
    //displayinhtml($commandeTable);
    $cmdInfo=$commandeTable[1];
    $order['fournisseur']=$cmdInfo['fournisseur'];
    //var_dump($cmdInfo);
}
//----------------------------------------------------------------------
//  créer ou dupliquer commande
include "0200-createCommande.php";

//----------------------------------------------------------------------
// Get Data
//
//   List of commandes

if ($order['fournisseur']){
    $where.=" and fournisseur='".$order['fournisseur']."'";
}

$activeWhere=" active=1"; // by default all active commands
$erase=false; // variable to erase commands where trash is selected
$classErase="white";
switch ($order['progress']){
    case "":
        break;
    case "0": // trash
        $activeWhere="active=0";
        $erase=true; // will really erase when trash is selected
        $classErase="realErase";
        break;
    default: 
        $where.=" and progress=".$order['progress']; //selection
}


$commandeQuery="SELECT * FROM prod_commande where $activeWhere  $where order by date_livraison_prevue;";
//$table=display_query($commandeQuery);
$commandeTable=query_table_dico($commandeQuery);
//echo $commandeQuery;
//var_dump($commandeTable[1]);
//echo "<br><br>";

$authorQuery="SELECT id,nom, prenom from prod_user";
$author_dico_table=query_table($authorQuery);
$authorDico=create_one_field_dictionnary($author_dico_table,"id","prenom");


// Count nb of items in commande
$commandeNbQuery="SELECT commande_id, count(id) as nb,sum(quantite*prixAchat) as amount FROM prod_commandeList  group by commande_id;";
//echo $commandeNbQuery;
$commandeNbTable=query_table_dico($commandeNbQuery);
//var_dump($commandeNbTable);
//echo "ot1";
//dispArray($commandeNbTable[0]);
//echo "ot2";
$commandeNbDico=create_one_field_dictionnary($commandeNbTable,"commande_id","nb",0);
$commandeAmountDico=create_one_field_dictionnary($commandeNbTable,"commande_id","amount",0);

//var_dump($commandeNbDico);
//----------------------------------------------------------------------
// prepare menu
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);


$progressList=['commande créée','commande validée','commande livrée','facture vérifiée'];

//----------------------------------------------------------------------
// start html

echo "<h1>Liste des Commandes</h1>";
$newCommande="";
if ($order['fournisseur']){
    echo "<div class='chosen'>".$order['fournisseur']."-".$dico['fournisseur'][$order['fournisseur']]."</div>";
    //---------------------------------
    // menu création Commande
    $newCommande="<div id='creerCommande'>";
    $newCommande.="<div>Créer une nouvelle commande</div>";
    $newCommande.="<div>Date de création:</div><div><input type='date' name='date_envoi' value='".date("Y-m-d")."' /></div>";
    $newCommande.="<div><span>Date de livraison prévue:</div><div><input type='date' name='date_livraison_prevue' /></div>";
    $newCommande.= "<div><button id='export' name='new'value=1 />créer une nouvelle commande </button></div>";
    if ($order['commande']!=""){
        $newCommande.= "<div><button id='export' name='duplicate' value='".$order['commande']."' />copier </button></div>";
    }
    //$newCommande.="</div>";
    $newCommande.=$failedCreateCommande;
    $newCommande.= "</div><br>";
    //---------------------------------
}
else{
    echo "<div class='chosen '>Fournisseur non sélectionné </div>";
    $selectedId="";
}

if ($order['commande']){
    $selectedId=$order['commande'];
    echo "<div class='chosen'>no ".$order['commande']." pour le ".$cmdInfo['date_livraison_prevue']."</div>";
}
else{
    echo "<div class='chosen'> Commande non sélectionnée</div>";
    $selectedId="";
}

echo "<br><form class='commande'>";

echo $newCommande;

$str= "<div class='container-fit'>";
$str.= "<div class='chooseList'>";
$str.="<table class='commTbl'>";
$str.="<tr id='cmdbtn'>
<td> </td>
<td>Envoi</td>
<td>Livraison</td>
<td>    <button class='gray' name='progress' value=''><img src='../0101-images/home.png' class='imgIcon'/></button>
        <button class='progress1' name='progress' value='1' >val</button>
        <button class='progress2' name='progress' value='2'>liv</button>
        
</td>
<td>Exporté</td>
<td>nombre <br>d'articles</td>
<td>total</td>";
if ($_SESSION['userInfo']['admin']){
    $str.="<td>fourn.</td>";
    $str.="<td>auteur</td>";
    $str.="<td><button class='gray' name='progress' value='0'><img src='../0101-images/trash.png' class='imgIcon'/></button></td>";
}
$str.="</tr>";
//var_dump($commandeTable);
foreach($commandeTable as $idx=>$row){

    //dispArray($row);
    $comId=$row['id'];
    $dateEnvoi=$row['date_envoi'];
    $dateLivraison=$row['date_livraison_prevue'];
    if ($comId==$order['commande']){$class="class='chosen'";}else{$class="";};
    
    $progressTitle=$progressList[$row['progress']];
    $str.= "<tr>
        <td><button name='commande' value='".$comId."' $class>".$comId."</button></td>
        <td><button name='commande' value='".$comId."' $class>".$dateEnvoi."</button></td>
        <td><button name='commande' value='".$comId."' $class>".$dateLivraison."</button></td>";
    
        
    $str.="<td class='progress".$row['progress']."'>".$progressTitle."</td>";
    $classnb="";
    if (isset($commandeNbDico[$comId])){
        $nb=$commandeNbDico[$comId];
        $amount=mynumber_format($commandeAmountDico[$comId],2);
    }
    else{
        $classnb="nbzero";$nb=0;
        $amount=0;
    };
    
    if ($row['exportAem']){
        $classExport="progress1";
    }
    else{
        $classExport="progress2";
    }
    $str.="<td class=$classExport >".$row['exportAem']."</td>";
    $str.="<td><button name='commande' value='".$comId."' $class>".$nb."</button></td>";
    $str.="<td><button name='commande' value='".$comId."' $class>".$amount."</button></td>";
    if ($_SESSION['userInfo']['admin']){
        $str.="<td>".$row['fournisseur']."</td>";
        $str.="<td>".$authorDico[$row['author']]."</td>";
    }
    $str.="<td class='$classErase'><img src='../0101-images/redCross.png' class='imgErase' value='$comId'></button></td>";
    if ($erase){ // button revive...
        $str.="<td class='recover'><img src='../0101-images/pharma.png' class='imgPharma' value='$comId'></button></td>";
        }
    $str.="   </tr>";
    
    
}
$str.="</table>";
$str.= "</div>";
$str.= "</div>";   
$str.= "</form>";
echo $str;
?>
<script>
$(document).ready(function() {
    $('.imgErase').click(function() {
        id=$(this).attr("value");
        <?php
        if ($erase){
            echo "if (confirm('Voulez vous vraiment effacer definitivement la commande no '+id+'?')) {";
        }
        else{
            echo "if (confirm('Voulez vous vraiment effacer la commande no '+id+'?')) {";
        }
            
        ?>
            console.log("pere");
            console.log($(this).parents().first().parents().first().html());
            $(this).parents().first().parents().first().remove();
            erase(id);
        }
        
    });

    function erase(id){
        <?php
        if ($erase){
            echo "query='DELETE from prod_commande WHERE id='+id;";
        }
        else{
            echo "query='UPDATE prod_commande SET active=0 WHERE id='+id;";
        }
        ?>
        console.log(query);
        //alert('Ne pas oublier de rafraichir la page');
            // modified: POST
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: { query: query},
                    success: function(response){ 
                                        
                        $(this).html(response);
                      
                    } 
            });
    }


// display tri column
    $('#itemsList').on('click','#tridisp',function(){
        console.log("tridisp clicked");
        console.log($('#tridisp').attr("class"));
        $('#tridisp').toggleClass("clicked");
        $('#tri').attr("class","icon tri");
        filteredList();
        
    });
    
    
    // recover commandes
    $('.imgPharma').click(function() {
        id=$(this).attr("value");
        query='UPDATE prod_commande SET active=1 WHERE id='+id;
        $(this).parents().first().parents().first().remove();
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: { query: query},
                    success: function(response){ 
                        
                        
                        console.log(response);
                    } 
            });
    });
  
});
    $(".leftNav").find("div").eq(1).addClass('navSelected');
    $('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#commandes");
})
</script>


