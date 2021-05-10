<?php
if (1==0){
    // get Query
    //$query=$_REQUEST['query'];
    //$query=substr($query,1,strlen($query)-3);
    $query="SELECT * FROM prod_articles WHERE departement<11 ";

    $table=query_table($query,1);
    //displayinhtml($table);

    // update prices and calculate


    $tableDico=query_table_dico($query);

    $query="SELECT * FROM prod_departement";
    $dicoDepartementMarque= create_one_field_dictionnary_sql($query,"id","marque");
    $dicoDepartementTitre= create_one_field_dictionnary_sql($query,"id","titre");
    echo "<table>";
    foreach ($dicoDepartementMarque as $id=>$val){
            echo "<tr><td>$id</td><td>".$dicoDepartementTitre[$id]."</td><td>".$dicoDepartementMarque[$id]."</td></tr>";
    }
    echo "</table>";
    //var_dump($dicoDepartementTva);

    // mise à jour des prix.
    //
    $query="";
    foreach ($tableDico as $row){
        $departement=$row['departement'];
        $row['marque']=16.667;
        if ($row['departement']!=0){
            $row['marque']=$dicoDepartementMarque[$departement];
        }
        echo "<br>".$row['designation']."tva".$row['tva']."marque".$row['marque']." dept".$row['departement']."<br>";
        $row['prixVente']=(1+(($row['tva']==1)?0.055:0.2))/(1-$row['marque']/100)*$row['prixAchat'];
        //dispArray($row);
        //does article exist?
        $query="SELECT * FROM prod_prices WHERE thedate='".date("Y-m-d")."' and ean=".$row['ean']." and source='popAchat';";
        $tableOneArticle=query_table($query);
        if (sizeof($tableOneArticle)>1){
            if (abs($row['prixVente']-$tableOneArticle[1]['prixVente'])>0.02) {
                $query="UPDATE prod_prices 
                SET prixAchat=".$row['prixAchat'].",prixVenteCalcule=".$row['prixVente'].",marque=".$row['marque'].",source='popAchat',author=".$_SESSION['userInfo']['userId']."
                WHERE id=".$tableOneArticle[1]['id'].";";
                //echo htmlentities($query);
                echo "<br>".$query."<br>";
                simple_query($query);
            }
        }
        else{
            
                $query="INSERT into prod_prices (thedate,ean,prixAchat,prixVenteCalcule,marque,source,author) 
                    VALUES ('".date("Y-m-d")."','".$row['ean']."','".$row['prixAchat']."','".$row['prixVente']."','".($row['marque'])."','popAchat',".$_SESSION['userInfo']['userId'].");";
                simple_query($query);
                echo $query;
            
            
            //echo "<br>".$query."<br>";
            //simple_query($query);
        }
        //$query="UPDATE prod_articles SET prixVente=".$row['prixVente']." WHERE ean=".$row['ean'].";";
        
    }
}
// check prices
$query="SELECT ean,max(thedate) as thedate FROM prod_prices group by ean";
$queryPrice="SELECT B.* FROM ($query) as A left outer join prod_prices as B on A.thedate=B.thedate AND A.ean=B.ean";
//echo $queryPrice;

$function="
DELIMITER //

CREATE FUNCTION fnmarque (prixAchat FLOAT,prixVente FLOAT,tva FLOAT) 
RETURNS FLOAT 
DETERMINISTIC
BEGIN
DECLARE MARQUE FLOAT;
DECLARE tax FLOAT;
SET marque = 0; IF (tva=1) THEN SET tax='1.055'; ELSEIF (tva=2) THEN SET tax='1.2'; ELSE SET tax='1.1'; 
END IF ; 
SET marque = 1-prixAchat/prixVente*tax; 
RETURN marque; 
END;
// DELIMITER";

$query="SELECT B.thedate,A.ean,A.designation,A.prixAchat,A.prixVente,B.prixVenteCalcule,B.marque,format(100*fnmarque(A.prixAchat,A.prixVente,A.tva),2) as marqueAEM,
format(A.prixVente-B.prixVenteCalcule,3) as diff from (select * from prod_articles where departement<11) as A 
left outer join ($queryPrice) as B on A.ean=B.ean";
$query="SELECT *,abs(diff) as absval from ($query) as A where abs(diff)>0.02";
$query="SELECT thedate,ean,designation,prixAchat,prixVente,prixVenteCalcule,marque,marqueAEM,diff as difference from ($query) as A order by absval desc";
$table=query_table($query,1);
displayinhtml($table);



include "../0021-functions/0506-exportToXls.php";




$str=exportToXls("marque.xls",$table,["marque"]);
echo "<a href='./files/marque.xls'>marque.xls</a>";

?>
