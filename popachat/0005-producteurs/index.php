<?php
@session_start();
//var_dump($_SESSION);
if (isset($_REQUEST['fournisseur'])){
    header("Location:0012-commandes.php");
}
//var_dump($_SESSION);
include "0000-initFilesProd.php";
$_SESSION['rupture']=0;
//include "../0000-head.php";
//include "../0002-login.php";
$addPath="./";
echo myheader("<link rel='stylesheet' href='./documentation/style.css?v1.1'>",$addPath);
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);

echo "<h1> Bienvenue sur le site Producteurs</h1>";


echo "<p>Sur ce site vous pourrez consulter les produits vendus par Pop la Coop en fonction des fournisseurs
préparer des commandes, connaître les stocks, savoir ce qui a été vendu sur une période donnée.</p>";
echo "<div class='summary'>
<div>
<h3>Résumé: pour préparer une commande</h3>
<p><ol>
<li>Choisir un fournisseur</li>
<li>Choisir ou créer une commande</li>
<li>Consulter la liste des produits et les ajouter à la commmande</li>
<li>Ajuster les quantités sur la commande</li>
<li>Visionner le bon de commande</li>
</ol></p>
</div>
</div>";



echo "<p><a href='0002-intro.php' target='_blank'>Préparer une commande: pour les impatients</a></p>";

//----------------------------------------------------------------------
// La FAQ

$itemList=['user'=>['id','NomPrenom','SELECT id,concat(prenom," ",nom) as NomPrenom from prod_user']];
$list=[];
foreach ($itemList as $item=>$tab){
    $key=$tab[0];
    $val=$tab[1];
    $query=$tab[2];
    $table=query_table($query);
    $dico[$item]=create_one_field_dictionnary($table,$key,$val);

}

echo "<p><a href='documentation/blog.php' target='_blank'>Accès au Blog</a></p>";
$query="SELECT * from forum limit 1";
$tableMsg=query_table_dico($query);
echo "<table class='blog'>";
    echo "<tr><th>Auteur</th><th>Date</th><th>Difficulté</th><th>Réponse</th></tr>";
    foreach ($tableMsg as $row){
        echo "<tr>
            <td>".$dico['user'][$row['author']]."</td>
            <td>".$row['datetime']."</td>
            <td>".$row['message']."</td>";
        echo "<td>".$row['answer']."</td>";
     
        echo "</tr>";
        
    }
    echo "</table>";


//------------------------------------------------------------------------
//include "0002-intro.php";
echo "
<div class='lesLiens'>
<div >
<h1>Des liens utiles</h1>
<div><a href='https://popsondage.ml'>Sondages</a></div>
<div><a href='https://membres.poplacoop.fr/'>Gestion des membres</a></div>
<div><a href='http://agora.poplacoop.fr/'>Agora</a></div>
<div><a href='https://app.cagette.net/user/login'>Cagette</a></div>";

if ($_SESSION['userInfo']['admin']){
    echo "<div><a href='documentation/manuel-administrateur.php' target='_blank'>Manuel pour l'administrateur</a></div>";
    echo "<div><a href='../0003-documents/index.php' target='_blank'>Procedures et manuels</a></div>";
  
}

echo "</div>
</div>";




echo "</div>";
echo "</body>
</html>";
?>
