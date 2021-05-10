<?php
@session_start();
//var_dump($_REQUEST);

include "0000-initFilesProd.php";
//var_dump($_REQUEST['fournisseur']);
if (isset($_REQUEST['fournisseur'])){
    $query="SELECT * from prod_fournisseur where id='".$_REQUEST['fournisseur']."';";
    $table=query_table($query);
    $_SESSION['myRange']=$table[1]['frequenceCommandes'];
    $_SESSION['fournisseur']=$_REQUEST['fournisseur'];
    $_SESSION['commande']="";
    
    //die;
    header("Location:0012-commandes.php");
}

if (isset($_REQUEST['nouveauFournisseur'])){
    header("Location:0019-addFournisseur.php?action=new");
}
if (isset($_REQUEST['modifierFournisseur'])){
    header("Location:0019-addFournisseur.php?action=modify&fournisseur=".$_SESSION['fournisseur']);
}
if (isset($_REQUEST['formatXls'])){
    header("Location:0411-formatXlsImport.php");
}

//header("Location:0411-formatXlsImport.php");



echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);


//dico['fournisseur'.'alf']
//var_dump($dico);


echo "<h1>Fournisseur</h1>";
echo "<form>";
 
//echo "fournisseur=".$order['fournisseur']."<br>";
if ($order['fournisseur']){// display chosen only if... chosen!
    $selectedId=$order['fournisseur'];
    echo "<div class='chosen'>".$order['fournisseur']."-".$dico['fournisseur'][$order['fournisseur']]."</div>";
}

echo "<div id='fournisseurMenu'>";
//if ($_SESSION['userInfo']['admin']==1){// Display modified if super user.
    echo "<div><button name='nouveauFournisseur'>Nouveau fournisseur</button></div>";
//}
if ($order['fournisseur']){
        echo "<div><button name='resetFournisseur'>Désélectionner</button></div>";
        //if ($_SESSION['userInfo']['admin']==1){// Display modified if super user.
            echo "<div><button name='modifierFournisseur'>Modifier un fournisseur</button></div>";
        //}
    }
else{
    echo "<div class='chosen orange'> </div>";
    $selectedId="";
}
if ($_SESSION['userInfo']['admin']==1){// Display modified if super user.
    echo "<div><button name='formatXls'>FormatXls</button></div>";
}
echo "</div>";
echo "</form>";
//var_dump($dico);

echo "<br>";
echo "<form class='fournisseur'>";
echo "<div class='container-fit'>";
    echo "<div class='chooseList'>";
        echo "<table class='fourTbl' ><tr><th onclick='setTriAlf(0);'><img  class='icon' src='../0101-images/uprightarrow.png'>
        <div class='aide'>tri dans l'ordre croissant ou décroissant</div>
        </th>
        <th onclick='setTriAlf(1);' ><img class='icon' src='../0101-images/uprightarrow.png'>
        <div class='aide'>tri dans l'ordre alphabétique croissant ou décroissant</div></th></tr>";
        //               le dico, tbl suffix, 
        $tri="";
        
        
        if($order['alf']<2){$tri='alf';if ($order['alf']==1){$tri.='desc';}}
        else{$tri='';if ($order['alf']==3){$tri.='desc';}}
        
        
        echo tableChoose($dico,"fournisseur",$order['fournisseur'],$tri);
        echo "</table>";
    echo "</div>";
echo "</div>";
echo "<input id='alf' name='alf' type='hidden' value='".$order['alf']."'></input>";
echo "</form>
<a href='' id='next'>ETAPE SUIVANTE</button>
";
?>

<script>
function setTriAlf(val){
    console.log('alf='+val);
    valAlf=$('#alf').attr('value');
    if (val==1){
        if(valAlf<2){valAlf=1-valAlf;}else{valAlf=0}
    }
    else{
        if (valAlf>1){valAlf=5-valAlf;}else{valAlf=2}
    }
    $('#alf').attr('value',valAlf);
    
    $('form').submit();
}  

$( document ).ready(function() {

$('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#fournisseur");
});

$('#next').click(function(){
    console.log( 'etape suivante' );
    $(this).attr("href", "./0012-commandes.php");
});


    console.log( 'ready!' );
});
$(document).ready(function(){
    $(".leftNav").find("div").eq(0).addClass('navSelected');
});

</script>






