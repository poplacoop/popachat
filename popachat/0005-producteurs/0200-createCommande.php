<?php
//----------------------------------------------------------------------
// Create Commande
//----------------------------------------------------------------------
$failedCreateCommande="";
if (($order['new']!="")||($order['duplicate']!="")){
    $noLoad=false;
    if ($order['date_livraison_prevue']==""){
        $failedCreateCommande="<br><span class='failed'>Vous n'avez pas renseigné la date de livraison</span>";
        $noLoad=true;
    }
    if ($order['date_envoi']==""){
        $failedCreateCommande="<br><span class='failed'>Vous n'avez pas renseigné la date d'envoi</span>";
        $noLoad=true;
    }
    if ($order['fournisseur']==""){
        $failedCreateCommande="<span class='failed'>Vous n'avez pas choisi de fournisseur</span>";
        $noLoad=true;
    }
}
//----------------------------------------------------------------------
// Insert brand new command
if ($order['new']!=""){
    if(!$noLoad){
        $query="INSERT INTO `prod_commande` ( date_envoi,`date_livraison_prevue`,`date_livraison_effective`, `fournisseur`, `author`) 
        VALUES ( '".$order['date_envoi']."','".$order['date_livraison_prevue']."','".$order['date_livraison_prevue']."', '".$order['fournisseur']."', '".$_SESSION['userInfo']['userId']."');";
        simple_query($query);
        $query="SELECT * FROM prod_commande WHERE fournisseur=".$order['fournisseur']." ORDER BY ID DESC";
        $result=query_table($query);
        $commandeId=$result[1]['id'];
        $_SESSION['commande']=$commandeId;
        header("Location:0012-commandes.php");
    }
}
//----------------------------------------------------------------------
// duplicate
if (($order['duplicate']!="")&&(!$noLoad)){
    $sortie=0;
    for($nb=0;$sortie==0;$nb++){
        $query="SELECT * FROM prod_commande WHERE id='".($nb+1)."'";// has been too far because of $nb++
        $table=query_table($query);
        if (sizeof($table)==1){
            //echo $query;
            //echo $nb." ";
            //$commandeId=$nb;
            $sortie=1;
        }
        if ($nb>90){$sortie=1;}
    }
     
    $query="INSERT INTO `prod_commande` ( id,date_envoi,`date_livraison_prevue`, `fournisseur`, `author`) 
    VALUES ( '$nb','".$order['date_envoi']."','".$order['date_livraison_prevue']."', '".$order['fournisseur']."', '".$_SESSION['userInfo']['userId']."');";
    //echo $query."<br>";
    simple_query($query);
    
    
    //$query="SELECT * FROM prod_commande WHERE fournisseur='".$order['fournisseur']."' ORDER BY TIMESTAMP DESC";
    //$result=query_table($query);
    //

    // erase residual data in prod_commandList if there is, just in case
    $query="DELETE FROM prod_commandeList WHERE commande_id='".$nb."';";
    simple_query($query);
    
    
    // look for items is copied list.
    $query="SELECT * FROM prod_commandeList WHERE commande_id='".$_SESSION['commande']."';";
    //echo $query."<br>";
    $result=query_table($query,1);
    //displayinhtml($result);

    for($i=1;$i<sizeof($result);$i++){
        $row=$result[$i];
        if ($row['quantite']!=0){
            $query="INSERT into prod_commandeList (commande_id,ean,quantite,prixAchat) VALUES ('$nb','".$row['ean']."','".$row['quantite']."','".$row['prixAchat']."');";
            echo $query."<br>";
            simple_query($query);
        }
        
    }
    $commandeId=$nb;
    $_SESSION['commande']=$commandeId;
    
    header("Location:0012-commandes.php"); // allows to load new values...
}
?>
