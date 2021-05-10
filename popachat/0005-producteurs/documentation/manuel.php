<?php
include "../../0000-head.php";
include "../../0021-functions/0505-miscellaneousFunctions.php";
$addPath="../";
echo myheader("<link rel='stylesheet' href='style.css?v2.2'>",$addPath);
?>

<body class='doc'>

	<h1>Aide détaillée pour l'utilisation de Pop Achat</h1>
    <button id="retour" value="retour">RETOUR</button>
    <p class='pageTitle'><a href='../0011-fournisseur.php'>1) Fournisseurs</a></p>
    <p>Dans ce menu on choisit le fournisseur. Il suffit de cliquer soit sur le numéro, soit sur le nom. L'application passe automatiquement sur la page "Liste des commandes". </p>
    <p>  Si on veut désélectionner un fournisseur, on clique sur  <button>Déselectionner</button> </p>
    
    <p>Si on clique sur le haut des colonnes sur les icones <img class="icon" src="../../0101-images/uprightarrow.png">.
    <ul><li>Au-dessus des numéros on les classe dans l'ordre croissant ou pas.</li>
    <li>Au-dessus des noms on les classe par ordre alphabétique ou pas.</li>
    </ul>
    
    <p>  Si on veut créer un fournisseur, on choisit <button>Nouveau Fournisseur</button></p>
    <p>  Si on veut modifier un fournisseur, le bouton <button>Modifier un fournisseur</button> le permet.</p>
    <p>Les deux boutons permettent d'ouvrir une nouvelle page. Pour plus d’informations, voir le paragraphe <a href='#ajoutFournisseur'>Ajout ou modification des fournisseurs</a></p>
  
    
 
  <p class='pageTitle'> <a id='commandes' href='../0012-commandes.php'>2) Liste des commandes</a></p>
  
  Toutes les commandes passées avec le fournisseur sélectionné sont présentées. Si aucun fournisseur n'a été sélectionné auparavant, la page présente toutes les commandes passées.
    <ul>
        <li>Indiquer une date de livraison</li>
        <li>Cliquer sur "créer une nouvelle commande" puis cliquer dans la liste des commandes sur la date de cette nouvelle commande créée pour passer à la suite</li>
        <li>ou Cliquer sur "copier" pour dupliquer une commande sélectionnée parmi les commandes de la liste.</li>
    </ul>
  
  On peut choisir de ne voir que les commandes validées (bouton rouge) ou les commandes livrées (bouton bleu).
  
  <p>ATTENTION, en cliquant sur la croix rouge <img style="width:20px" src="https://popachat.ml/0101-images/redCross.png"> , la commande passe dans la corbeille!</p>
  <p>Si une commande a été effacée par un utilisateur par erreur, il est possible de la récupérer. Adressez-vous aux administrateurs.</p>
  

  <p class='pageTitle'> <a id ='liste' href='../0013-liste.php'>3) Liste des produits</a></p>
  
  Toutes les références du fournisseur apparaissent. Si vous n'avez pas sélectionné de fournisseur, apparaissent toutes les références du magasin. <br>
    Pour chaque produit
    <ul><li>Cliquer sur le libellé du produit (En cliquant sur 
    <img style='width:20px' src="https://popachat.ml/0101-images/graph.png" >
     vous pouvez connaître l’évolution des ventes et des stocks sur une 
     période choisie).<a href='#stock'>Accéder à l'aide sur l'évolution des stocks.</a>
    <li>Cliquer sur le stylet à droite 
    <img  style='width:20px' src="https://popachat.ml/0101-images/pencil1600.png" ></img>
        et indiquer la quantité souhaitée. Le logiciel calcule le nombre de 
        colis correspondant et l'affiche dans la colonne colis. Cette quantité 
        ne peut être qu'un multiple du conditionnement (par exemple 12, 24, 36 pour une référence vendue par lots de 12). Le logiciel modifie cette quantité s'il ce n'est pas un multiple du conditionnement en la remplaçant par la valeur la plus proche: par exemple pour un conditionnement de 12, une quantité de 14 devient 12 pour 1 colis, une quantité de 19 devient 24 pour 2 colis.</li>
    <li>Alternativement, on peut cliquer sur le colis, 
    <img  style='width:20px' src="https://popachat.ml/0101-images/colis.png" >
    </img> dans ce cas on modifie les colis et les quantités s'ajustent. <br>Dans le cas des produits achetés
    au Kg, il faut modifier les colis pour obtenir les bonnes quantités.</li>
     </ul>
     <p>Reproduire la procédure pour tous les produits et cliquer sur <b>validation</b> dans le menu en haut à gauche.</p>
    
<p class='pageTitle'><a id='commande' href='../0014-commande.php'>4) Validation</a></p>
    <ul>
    <li>Dans le tableau récapitulatif de la commande, on peut modifier la quantité en cliquant sur le stylet à droite  ou le prix unitaire en cliquant sur le symbole € à droite (une information apparait dans un bandeau vert en bas pour savoir si la commande sera franco de port ou non).
  Attention il faut sortir de la cellule ou appuyer sur la touche entrée pour enregistrer les modifications des cellules. On observe que la valeur de colis est modifiée.</li>
  <li>Si on clique sur le bouton <button>APPLIQUER LA REMISE</button>, les prix de chaque article est recalculé. La remise a été définie dans la fiche fournisseur: c'est un nombre compris entre 0 et 1 (pour 20%, on saisira 0.2).
  <br>On peut retirer la remise en cliquant sur <button>ENLEVER LA REMISE</button></li>
        <li>Une fois la commande vérifiée, pour la valider, cliquer sur le sablier 
        <img style='width:20px' src="https://popachat.ml/0101-images/hourglass-512.png" >
     à côté de la date d’envoi. La commande est enregistrée lorsqu'apparait ce verrou :<img style='width:20px'  src="https://popachat.ml/0101-images/locked.png" > La commande est enregistrée.
     </li>
  </ul>
  
  <p>À réception, sur la page Commande     vous pouvez rentrer la date de livraison et de réception de la facture</p>
  <p class='pageTitle'><a id='bon' href='../0018-bon.php'>5) Bon de commande</a></p>
  <p> Sur ce menu vous pouvez télécharger votre bon de commande en PDF.</p>
  <p>Les fichiers pdf et excel sont disponibles en téléchargement et seront envoyés en pièce jointe dans le mail.</p>
  <p>Les cellules sont pré-remplies mais peuvent être modifiées: on met les adresses mail séparées par des virgules.</p>
  <p>Pour le moment les fichiers sont enregistrés mais pas encore archivés.</p>
  <p>Des cellules pré-remplies permettent d'envoyer le bon de commande (pdf) au fournisseur par mail</p>
  
<p class='pageTitle'><a id='creation' href='../0015-creationDeProduits.php'>6)  Création de produits: </a>
<p class='titre'>Création</p>
<p>Pour créer une référence, il est important de vérifier si le produit a une code barre ou non. S'il a un 
code barre, le renseigner dans la case prévue à cette effet, sinon laisser le champs libre: lors
de la création, le logiciel crééra un code disponible.</p>


<p><img class='imgWide' width='400' src='notice_creation_produit.jpg' /></p>


Pour créer un nouveau produit avec les même caractéristiques qu'un autre:
<ul>
<li>Dans création, sélectionner le produit existant.</li>
<li>Effacer le code barre.</li>
<li>Soit mettre le code barre connu, soit laisser la case blanche.</li>
<li>modifier la désignation et tous les paramètres qui sont différents.</li>
<li>cliquer sur Nouveau Produit: le produit est créé.</li>
</ul>

<p class='titre' >Modification</p>
Pour modifier une produit, le sélectionner dans la liste en bas de la page: le produit est chargé dans le formulaire.
Il s'agit alors de modifier les quantités et de cliquer sur "Modifier".



<p class='pageTitle'><a id='stock' href='../0016-gestionStocks.php'>7)  stock</a>
<p>Pour connaître les stocks</p>

Toutes les références du fournisseur sélectionné apparaissent. Si vous n'avez pas sélectionné de fournisseur, apparaissent toutes les références du magasin. 

<ul>
    <li>La dernière colonne permet de renseigner classer les produits:
        <ul>
        <li>Si la valeur est 0, le produit a été modifié et il faut l'importer
        sur store-pos.</li>
        <li>si la valeur est 1, le produit est actif.</li>
        <li>si la valeur est 2, le produit est inactif: par exemple
        le produit « fraises » lorsque ce n’est pas la saison des fraises.
        Le produit n'apparaît plus dans la liste des articles à commander.</li>
        <li>Si l'article doit être effacée de la base, on met la valeur 5.
        Les administrateurs à la prochaine maintenance effaceront l'article
        définitivement.</li>
        </ul>

<li>En cliquant sur 
    <img style='width:20px' src="https://popachat.ml/0101-images/graph.png" > 
    vous pouvez connaître l’évolution des ventes et des stocks <b>pour une 
    référence particulière sur une période choisie</b> (la période par défaut 
    est définie dans la fiche fournisseur en fonction de la fréquence
    des commandes)</li>
  <li>
Un curseur permet de choisir la durée (en nombre de semaines) pour la présentation des résultats.</li>
<li>Le graphe en barres présente les ventes (en bleu), les livraisons (en vert) et les corrections suite à inventaire (en rouge), pendant cette période; la courbe noire présente l'évolution du stock.</li>
  <li>
    Un tableau présente le nombre de ventes cumulées par période antérieure de la même durée (nombre variable 
    en fonction de la date d'introduction du produit).</li>
<li>Dans le deuxième tableau, sont indiquées le nombre total de ventes 
pour la période antérieure de la durée choisie et la durée d'écoulement 
prévisible pour le stock restant en fonction des ventes passées.</li>

</ul>

<img class='imgWide' width='400' src='notice_gestion_des_stocks.jpg' />

<p class='pageTitle'><a id='ajoutFournisseur' href='../0011-fournisseur.php'>8)  Ajout ou modification des fournisseurs</a>
<p>Pour créer un fournisseur, compléter les cellules obligatoires. Tant
que les cellules obligatoires ne sont pas renseignées, le fournisseur
n'est pas créé.</p>
<p>Pour modifier un fournisseur, compléter les cellules correspondantes. 
Par sécurité, il faut activer le bouton autorisation de modification avant
de cliquer le bouton modifier pour que la modification soit effective.</p>


<img class='imgWide' width='400' src='Creation_fournisseur.jpg' />

<script>
    $(document).ready(function() {
    $('#retour').click(function(){
        window.close();
    });
});
</script>

</body>
</html>



</html>
