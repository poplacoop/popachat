<?php
$menuFilter="fd";
if ($order['commande']){
    $menuFilter.="ob";
}
$menuFilter.="ecsr";
    
function menu($menu){
    $str="";
    if (isset($_SESSION['userInfo']['userRights'])){
        $menuList=['f'=>['fournisseur','0011-fournisseur'],'d'=>['commandes','0012-commandes'],
        'e'=>['selection','0013-liste'],'o'=>['validation','0014-commande'],'b'=>['bon','0018-bon'],
        'c'=>['creation','0015-creationDeProduits'],'s'=>['stock','0016-gestionStocks']];
        //,'r'=>['nouveau','0019-addFournisseur']];
        //    's'=>['stock','0016-gestionStocks']];  
            $str="
        <nav class='leftNav'>";
        $filterLetters=str_split($menu);
        //dispArray($letters);
        foreach ($menuList as $letter=>$val){
            //mp($letter);
            //dispArray($filterLetters);
            if (in_array($letter,$filterLetters)){
                $class='navActive';
                $str.= "   <div class='$class' ><a href='".$menuList[$letter][1].".php'>".$menuList[$letter][0]."</a></div>";
            }
            else{
                $class='navDisabled';
                $str.= "   <div class='$class' ><a href=''>".$menuList[$letter][0]."</a></div>";
            }
        } 
        if ($_SESSION['userInfo']['admin']){
            $str.= "   <div class='$class' ><a href='../indexAdmin.php'>admin</a></div>";
        };
    }
 
    $str.="</nav> 
    <nav class='rightNav'>
    <div><a href='../deconnexion.php'>Deconnexion</a></div>
        <div><a href='/index.php'>Accueil</a></div>
    <div><a id='help' href='documentation/manuel.php' target='_blank'>Aide</a></div>
    </nav> ";
    if (!isset($_SESSION['userInfo']['userRights'])){
        $str.="<div class='connexion'>Pour accéder au menu producteur demander une autorisation à  <a href='mailto:didier.cransac@gmail.com'>didier.cransac@gmail.com</a></div>";
    }
    
    return $str;
}

?>
