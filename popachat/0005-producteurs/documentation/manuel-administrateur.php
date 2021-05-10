<?php
include "../../0000-head.php";
include "../../0021-functions/0505-miscellaneousFunctions.php";
$addPath="../";
echo myheader("<link rel='stylesheet' href='style.css?v1.1'>",$addPath);
?>

<body class='doc'>

	<h1>Manuel de pour l'administrateur</h1>
    <button id="retour" value="retour">RETOUR</button>
    <p class='pageTitle'><a href='../0011-fournisseur.php'>Imports des fichiers de commande</a></p>
    <p>Choisir les colonnes pour chacun des attributes à mettre dans la base <code>prod_import_readxls</code></p>
    <p>Noter le numéro du format</p>
    <p>Dans <code>prod_import_fournisseurs</code>, créer un ligne pour le fournisseur et le format. S'il s'agit d'un fichier excel il faut
    appeler <code>0512-upload_excel.php</code></p>
    
   <h1>Gestion des articles</h1>
   Il y a 3 niveaux d'articles:
    <ul>
        <li>0) Articles nouveaux ou modifiés à importer dans la base aemsoft.</li>
        <li>1) Articles en cours</li>
        <li>2) Articles temporairement abandonnés</li>
    </ul>
    
    <h1>Commande: Articles et Prix</h1>    
    <p>L'import se fait avec le modèle IMPORTXLS DE BASE</p>
   
    <h1>creation d'articles</h1>    
    <p>L'import se fait avec le modèle IMPORTXLS2021/p>
   
   

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
