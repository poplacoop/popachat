<?php
@session_start();
include "0000-initFilesProd.php";
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);
?>
<button id="retour" value="retour">RETOUR</button>
<div class='intro'>
    
    <div>
    <h1 id='fournisseur' class='pages'>Pour préparer une commande</h1>
       Pour commencer, dans le menu en haut à gauche cliquer sur <a href='./0011-fournisseur.php'>Fournisseurs</a>.
       
        <p class='pageTitle'><a href='./0011-fournisseur.php'>1) Fournisseurs</a></p>
        Cliquer sur le fournisseur désiré.

        <p class='pageTitle'><a href='./0012-commandes.php'>2) Liste des commandes</a></p>
        <ul>
            <li>Indiquer une date de livraison.</li>
            <li>Cliquer "créer une nouvelle commande".</li>
            <li>Cliquer dans la liste des commandes sur la date créée.</li>
        </ul>  

        <p class='pageTitle'><a href='./0013-liste.php'>3) Liste des produits</a></p>
        Pour chaque produit :
            <ul>
                <li>Cliquer sur le libellé du produit
                (En cliquant sur <img  src='https://popachat.ml/0101-images/graph.png' >vous pouvez connaître l’évolution des ventes et des stocks sur
                une période choisie).</li>
                <li>Cliquer sur le stylet à droite <img src='https://popachat.ml/0101-images/pencil1600.png'></src>
                et indiquer la quantité souhaitée.</li>
            </ul>
        Reproduire pour tous les produits.

        <p class='pageTitle'><a href='./0014-commande.php'>4) Validation</a></p>
            Une fois la commande vérifiée et modifiée si nécessaire, valider la commande en cliquant sur le sablier
            <img style='width:20px' src="https://popachat.ml/0101-images/hourglass-512.png" >
            à côté de la date d’envoi. La commande est enregistrée lorsqu'apparait ce verrou :<img style='width:20px'  src="https://popachat.ml/0101-images/locked.png" >.
     

        <p class='pageTitle'><a href='./0018-bon.php'>5) Bon de commande</a></p>
        <p> Vous pouvez télécharger et envoyer par mail le bon de commande en PDF.</p>
    </div>

</div>
<script>
$(document).ready(function() {
    $('#retour').click(function(){
        window.close();
    });
});
</script>
</body>
</html>
    
