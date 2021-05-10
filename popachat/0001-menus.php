<?php
function menu($rights,$page){
    switch ($page){
        case '':
            $menuList=['a'=>['Présentation','/0001-accueil'],
            's'=>['Sondage','/0002-sondage'],
            'p'=>['Producteurs','/0005-producteurs'],
            'y'=>['Synthese','/0004-synthese'],
            'd'=>['Documents','/0003-documents'],
            'm'=>['Admin','/0010-admin']];
            break;
        case 'producteurs':
            $menuList=['f'=>['fournisseur','fournisseur'],
            'd'=>['commandes','commandes'],
            'e'=>['liste','liste'],
            'o'=>['commande','commande'],
            'b'=>['bon','bon']];
            break;
        case 'admin':
            $menuList=['i'=>['import','import' ]];
            break;
        case 'synthese':
            $menuList=['s'=>['synthese','synthese']];
            break;
    }
    if ($rights==""){
        $rights="a";
    }
    $letters=str_split($rights);// give acces only to rights
    $str="
    <nav class='leftNav'>";
    foreach ($letters as $letter){
        if (key_exists($letter,$menuList)){
            $str.= "   <div><a href='".$menuList[$letter][1]."/index.php'>".$menuList[$letter][0]."</a></div>";
        }
    }
    $str.="
    </nav> ";
    $str.="</nav> 
    <nav class='rightNav'>
    <div><a href='/deconnexion.php'>Deconnexion</a></div>
    <div><a href='/index.php'>Accueil</a></div>
    <div><a href='aide'>Aide</a></div>
    </nav> ";
    
    
    return $str;
}

?>
