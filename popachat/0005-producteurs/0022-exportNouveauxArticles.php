<?php
@session_start();
include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0501-graphFunctions.php";
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);

// get Query
//$query=$_REQUEST['query'];
//$query=substr($query,1,strlen($query)-3);
$query="SELECT ean,'' as vide,refFour,designation,departement,famille,conditionnement,contenance,unitecontenance,
prixAchat,prixVente,tva,'' as stock,fournisseur,'' as O,'' as P,'' as Q,'' as R,'' as S, '' as T, groupe,'' as V,if(uniteVente='Kg','1','0') as W
FROM prod_articles WHERE validated=0 ORDER BY TRI";

$table=query_table($query,1);
// treat EAN 000000
//include "../0021-functions/0410-getNewEan.php";

//var_dump($_REQUEST);

//displayinhtml($table);
// update prices and calculate
$tableDico=query_table_dico($query);

$query="SELECT * FROM prod_departement";
$dicoDepartementMarque= create_one_field_dictionnary_sql($query,"id","marque");
//var_dump($dicoDepartementTva);

$query="SELECT * FROM prod_fournisseur";
$dicoFournisseurDiscount= create_one_field_dictionnary_sql($query,"id","discount");
//var_dump($dicoFournisseurDiscount);
// mise à jour des prix only if departement is defined.
//
$query="";
$i=1;
foreach ($tableDico as $key=>$row){
    $ean=$row['ean'];
    //dispArray($row);
    //echo $ean."<br>";
    //echo substr($ean,6)."<br>";
    if (substr($ean,7)=="0000000"){
        $eanNb=substr($ean,0,12);
        $table[$i][0]=$eanNb.find_last_value($eanNb);
        $table[$i]['conditionnement']=1;//Mettre le conditionnement à 1
        $table[$i]['ean']=$eanNb.find_last_value($eanNb);// 
        
    }
    //echo "table $i<br>";
    //dispArray($table[$i]);
    //echo "<br>end of table";
    //echo $table[$i-1]['ean'];
    
    $departement=$row['departement'];
    if (($row['departement']!=0)&&($row['tva']!=0)){
        $row['marque']=$dicoDepartementMarque[$departement];
        
        if (isset($dicoFournisseurDiscount[$row['fournisseur']])){
            $discount=$dicoFournisseurDiscount[$row['fournisseur']];
        }
        else{
            echo "<div class='nouveau'>".$row['designation']." could not find discount for fournisseur ".$row['fournisseur']."</div>";
        }
        
        //echo "<br>".$row['designation']." tva=".$row['tva']." marque=".$row['marque']." discount=".$discount;
        $row['prixAchat']=number_format((1-$discount)*$row['prixAchat'],2);
        $row['prixVente']=number_format((1+(($row['tva']==1)?0.055:0.2))/(1-$row['marque']/100)*$row['prixAchat'],2);
        //dispArray($row);
        //does article exist?
        $query="SELECT * FROM prod_prices WHERE thedate='".date("Y-m-d")."' and ean=".$row['ean']." and source='popAchat';";
        $tableOneArticle=query_table($query);
        if (sizeof($tableOneArticle)>1){
            $query="UPDATE prod_prices 
            SET prixAchat=".$row['prixAchat'].",prixVenteCalcule=".$row['prixVente'].",source='popAchat',author=".$_SESSION['userInfo']['userId']."
            WHERE id=".$tableOneArticle[1]['id'].";";
            //echo htmlentities($query);
            echo "<br>".$query."<br>";
            simple_query($query);
        }
        else{
            $query="INSERT into prod_prices (thedate,ean,prixAchat,prixVenteCalcule,marque,source,author) 
            VALUES ('".date("Y-m-d")."','".$row['ean']."','".$row['prixAchat']."','".$row['prixVente']."','".$row['marque']."','popAchat',".$_SESSION['userInfo']['userId'].");";
            echo "<br>".$query."<br>";
            simple_query($query);
        }
        $query="UPDATE prod_articles SET prixVente=".$row['prixVente']." WHERE ean=".$row['ean'].";";
        simple_query($query);
        echo $query."<br>";
    }
    else{
        echo "Pas de departement ou de marque pour ".$row['ean'].":".$row['designation']."<br>";
    }
    //echo "<br>end of loop<br>";
    $i++;

}
//echo $table[];
//die;
//simple_query($query);


include "../0021-functions/0506-exportToXls.php";
$str=exportToXls("nouveauxArticles.xls",$table);
echo "<br><a href='./files/nouveauxArticles.xls'>nouveauxArticles.xls</a>";
echo $str;
// create Excel and html
?>

</body>
</html>

