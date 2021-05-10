<?php
@session_start();

require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0501-retrieveFunctions.php";
include "../0021-functions/0505-miscellaneousFunctions.php";
include "../0021-functions/0506-exportToXls.php";
include "0003-prepareSyntheseData.php";
//include "../0005-producteurs/0501-graphFunctions.php";
include "../0021-functions/0700-genericGraphFunctions.php";
//----------------------------------
// display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//----------------------------------
include "../0000-head.php";
include "../0002-login.php";
echo myheader();
echo "<body>";
$userRights=$_SESSION['userInfo']['userRights'];
// modify header
echo "<script>
$(function () {
            $(document).attr('title', 'pop Achat');
        });
</script>";
//----------------------------------------------------------------------
// check values
$menuList=['s'=>['synthese','0004-synthese.php'],'r'=>['retour','../indexAdmin']];  
$menuFilter="sr";

include '0001-menuSynthese.php';
echo "<body>
    <div class='topBanner'>";
echo menuSynthese($menuFilter);
echo "</div>";

//----------------------------------------------------------------------
// Create the body of the page
//----------------------------------------------------------------------

$menu=$order['menu'];
$menuList=['poorsales','articlestop100','depensearticlesbot100','camois','caweek','caday','analyseprix','tickets','stocks',
'cafe','legume','serge','connexions'];
echo "<table>";
foreach ($menuList as $item){
    echo "<tr>";
    echo "<td><a href='0004-synthese.php?menu=$item' >$item</a></td>";
    echo "</tr>";
}
echo "</table>";
//----------------------------------------------------------------------
// calcul des dépenses par article
//----------------------------------------------------------------------
if ($menu=='poorsales'){
    $myRange=8;
    $duration=7*$myRange;
    $limit=10000;
    $tri="ASC";
    $seuilCA=150;
    $ageThreshold=21;
    
    echo "Période $duration jours";
    $query="SELECT ean,quantite*prixVente as product FROM prod_plu where thedate>CURRENT_DATE - INTERVAL $duration DAY ";
    $query="SELECT ean,sum(product) as total FROM ($query) as A GROUP BY ean";
    $query1="SELECT ean,total FROM ($query) as A ORDER BY total $tri LIMIT $limit";
    $table=query_table($query1);
    //displayTableInHtml($table);

    $queryProductAge="SELECT ean,min(thedate) as minDate,datediff(current_date,min(thedate)) as productAge FROM prod_stock_mvt WHERE LEFT(bl,2)='BL' GROUP BY ean  
        ORDER BY `productAge`  DESC";

    $queryProductAge="SELECT ean,minDate,if(productAge>".(7*$myRange).",".(7*$myRange).",productAge) as productAge FROM ($queryProductAge) as A";
    $query="SELECT ean FROM ($query1) as A";
    //echo $query;
    $query2="SELECT ean,designation,famille,departement,fournisseur,validated from prod_articles where ean in ($query) ";
    //$table=query_table($query2);
    //displayTableInHtml($table);
    $query="SELECT A.ean,B.designation,B.fournisseur,B.famille,B.departement,B.validated,A.total FROM ($query1) AS A LEFT OUTER JOIN ($query2) as B ON A.ean=B.ean order by total $tri";
    $query="SELECT A.*,B.minDate,B.productAge from ($query) as A LEFT OUTER JOIN ($queryProductAge) as B on A.ean=B.ean where B.ProductAge>$ageThreshold";
    $query="SELECT *,A.total/A.productAge as ratioCAperDay from ($query) as A where total< $seuilCA order by A.total,departement,famille $tri";
    //$list=
    
    foreach (['fournisseur','departement','famille'] as $word){
        $query="SELECT A.*,B.titre as ".$word."name from ($query) AS A left outer join prod_$word as B on A.$word=B.id";
        //echo $query."<br><br>";
    }

    $table=query_table($query);
    
    //echo $query;
    echo "<br>";
    //----------------------------------------------------
    // create Excel and html
    //include "../0021-functions/0506-exportToXls.php";
    $str=exportToXls("export.xls",$table,['exclure','rupture']);
    echo "<a href='./files/export.xls'>export.xls</a>";
    echo $str;
    displayinhtml($table);
    
}
//----------------------------------------------------------------------
// articles top 100
//----------------------------------------------------------------------
if ($menu=='articlestop100'){
    $myRange=8;
    $duration=7*$myRange;
    $limit=10000;
    $tri="ASC";
    $seuilCA=200;
    $ageThreshold=21;
    echo "<h1>top 50</h1>";
    echo "Période $duration jours";

    $query="SELECT ean,quantite*prixVente as product FROM prod_plu where thedate>CURRENT_DATE - INTERVAL $duration DAY ";
    $query="SELECT ean,sum(product) as total FROM ($query) as A GROUP BY ean";
    $query1="SELECT ean,total FROM ($query) as A ORDER BY total DESC ";
    //$table=query_table($query1);
    //displayTableInHtml($table);


    $query="SELECT ean FROM ($query1) as A";
    //echo $query;
    $query2="SELECT ean,designation,dateIntroduction from prod_articles where ean in ($query) ";
    //$table=query_table($query2);
    //displayTableInHtml($table);
    $query="SELECT B.ean,B.designation,A.total,B.dateIntroduction FROM ($query1) AS A LEFT OUTER JOIN ($query2) as B ON A.ean=B.ean where total >$seuilCA order by total desc";
    $table=query_table($query);
    //echo $query;
    echo "<br>";
    //----------------------------------------------------
    // create Excel and html
    //include "../0021-functions/0506-exportToXls.php";
    
    //echo $str;
    $newTable=$table;
    array_shift($newTable);
    $ruptureArray=[];
    foreach ($newTable as $key=>$row){
        //dispArray($row);
        // select values where stock is negative or null
        $query="SELECT * from prod_stock where ean=".$row['ean']." and thedate>CURRENT_DATE - INTERVAL $duration DAY  and stock<=0";
        // Select end of week and date > date introduction
        $query="SELECT * from ($query) as A where thedate>'".$row['dateIntroduction']."' and abs(weekday(thedate)-4)<2";
        
        //details of dates
        $query="SELECT B.designation,A.thedate from ($query) as A LEFT OUTER JOIN ($query2) as B ON A.ean=B.ean order by B.designation, A.thedate";
        $temptable=query_table($query);
        //var_dump($temptable);
        if (sizeof($temptable)>1){
            array_shift($temptable);
            $designation=$temptable[0]['designation'];
            $ruptureArray[$designation]=[];
            foreach ($temptable as $row){
                array_push($ruptureArray[$designation],$row['thedate']);
            }
        }
    }
    $ruptTable=[['designation','dataRupture']];
    // create table of rutpure...
    echo "<table>";
    foreach ($ruptureArray as $design=>$arr){
        //var_dump($arr);
        foreach ($arr as $item){
            //echo "<tr><td>$design</td><td>$item</td></tr>";
            array_push($ruptTable,[0=>$design,1=>$item,'designation'=>$design,'dataRupture'=>$item]);
        }
    }
    echo "</table>";
    $file="rupture.xls";
    $str=exportToXls($file,$ruptTable,['include']);
    echo "<a href='./files/$file'>$file</a>";
    echo "<br>nb of lines ".sizeof($table)."<br>";
}
//----------------------------------------------------------------------
// calcul des dépenses par article
//----------------------------------------------------------------------
if ($menu=='depensearticlesbot100'){
    $duration=60;
    echo "Période $duration jours";
    $query="SELECT ean,quantite*prixVente as product FROM prod_plu where thedate>CURRENT_DATE - INTERVAL $duration DAY ";
    $query="SELECT ean,sum(product) as total FROM ($query) as A GROUP BY ean";
    $query1="SELECT ean,total FROM ($query) as A ORDER BY total ASC ";
    //$table=query_table($query1);
    //displayTableInHtml($table);


    $query="SELECT ean FROM ($query1) as A";
    //echo $query;
    $query2="SELECT ean,designation from prod_articles where ean in ($query) ";
    //$table=query_table($query2);
    //displayTableInHtml($table);
    $query="SELECT B.designation,A.total FROM ($query1) AS A LEFT OUTER JOIN ($query2) as B ON A.ean=B.ean where total <10 order by total asc";
    $table=query_table($query);
    displayinhtml($table);
}
//----------------------------------------------------------------------
// calcul du CA par mois
//
if ($menu=='camois'){
    echo "<h2>Calcul du Chiffre d'Affaire par mois</h2>";
    //include "502-trialfunctions.php";
    echo "<br>";
    echo "<h2>CA month</h2>";
    $query="SELECT year(thedate) as year,month(thedate) as month, sum(cattc)as cattc,sum(caht)as caht,sum(quantite*prixAchat) as caachat FROM `prod_plu`group by year,month";
    //$query="SELECT year,month,floor(cattc) as cattc,floor(caht) as caht,floor(caachat) as caachat,floor(caht-caachat) as benefice FROM ($query) as A";
    $query="SELECT year,month,floor(cattc) as cabrutttc,floor(caht) as caht,floor(caachat) as caachat,floor(caht-caachat) as benefice FROM ($query) as A";

    $table=query_table($query);
    //displayinhtml($table);
}
if ($menu=='caweek'){    
    echo "<h2>CA week</h2>";
    $query="SELECT thedate, (week(thedate) +52*(year(thedate)-2020)) as week,sum(cattc)as cattc,sum(caht)as caht,sum(quantite*prixAchat) as caachat FROM `prod_plu`group by week";
    echo $query;
    $query="SELECT thedate,week,floor(cattc) as cabrutttc,floor(caht) as caht,floor(caachat) as caachat,floor(caht-caachat) as benefice FROM ($query) as A";

    $table=query_table($query);
    //displayinhtml($table);
}
if ($menu=='caday'){    
    echo "<h2>CA day</h2>";
    $query="SELECT thedate, (week(thedate) +52*(year(thedate)-2020)) as week,sum(cattc)as cattc,sum(caht)as caht,sum(quantite*prixAchat) as caachat FROM `prod_plu`group by thedate";
    echo $query;
    $query="SELECT thedate,week,floor(cattc) as cabrutttc,floor(caht) as caht,floor(caachat) as caachat,floor(caht-caachat) as benefice FROM ($query) as A";

    $table=query_table($query);
    //displayinhtml($table);
}
//----------------------------------------------------------------------
// determination des anomalies de prix
//
if ($menu=='analyseprix'){
    echo "<h2>Analyse des prix</h2>";
    $query="SELECT plu.thedate,plu.ean,caht,100*(1-plu.prixAchat/plu.prixVente) as marque,plu.prixAchat,plu.prixVente,plu.quantite FROM prod_plu as plu 
    LEFT OUTER JOIN prod_articles ON plu.ean=prod_articles.ean 
    where thedate>CURDATE() - INTERVAL 2 WEEK";
    $table=query_table($query);
    //displayTableInHtml($table);

    $query="select thedate,ean,marque,prixAchat,prixVente,quantite from ($query) as a where abs(marque-16.1167)>3  order by marque";
    $table=query_table($query);

    $detailquery="select a.thedate,a.ean,format(a.marque,1) as marque,a.prixAchat,a.prixVente,
    format(100*(a.prixVente/a.prixAchat-1),1) as margePourcent,format((a.prixVente-a.prixAchat),3) as margeEuro,
    format(a.quantite,2) as quantite,b.designation from ($query) as a left outer join prod_articles as b on a.ean=b.ean order by marque";

    //$query="select a.ean,
    $detailtable=query_table($detailquery);
    //displayinhtml($detailtable);
    $table=$detailtable;
    /* try to understand below
    $limit=10000;

    $query="select a.ean,a.quantite*a.prixVente as CAVente,a.quantite*a.prixAchat as CAAchat from ($detailquery) as a";
    $query="select a.ean,sum(CAVente) as prixVente,sum(CAAchat) as prixAchat from ($query) as a group by ean";
    $query="SELECT plu.ean,100*(1-plu.prixAchat/plu.prixVente) as marque,plu.prixAchat,plu.prixVente,(plu.prixVente-plu.prixAchat) as benefice,
             prod_articles.designation FROM ($query) as plu LEFT OUTER JOIN prod_articles ON plu.ean=prod_articles.ean order by marque asc LIMIT $limit";
    //$table=query_table($query);
    //displayTableInHtml($table);
    $error=0;
    $query="SELECT ean,A.marque,prixAchat as montantAchat,prixVente as montantVente,benefice,designation from ($query) as A where abs(marque-16.1167)>=$error and designation like '%MACHE%'";
    $table=query_table($query);
    displayinhtml($table);
    $query="SELECT tot.*,art.prixAchat,art.prixVente from ($query) as tot left outer join prod_articles as art on tot.ean=art.ean;";
    $table=query_table($query);
    //displayTableInHtml($table);
    $headers=['ean','marque','Montant total dépensé','Montant total vendu','Bénéfice','Désignation','prixd\'Achat','prix de Vente'];
    $keys=['ean','marque','montantAchat','montantVente','benefice','designation','prixAchat','prixVente'];
    $nb=[0,1,1,1,1,0,1,1];
    $str="";
    $headStr= "<tr>";
    foreach($headers as $val){
            $headStr.="<th>".$val."</th>";
    }
    $headStr.= "</tr>\n";
    $str="";
    for($i=1;$i<sizeof($table);$i++){
            $row=$table[$i];
            $str.= "<tr>";
            //var_dump($filter);
            foreach($keys as $idx=>$key){
                if ($nb[$idx]){
                    $val=number_format($row[$key],2);
                }
                else{
                    $val=$row[$key];
                }
                
                $str.="<td>".$val."</td>";
                }
            $str.="</tr>";
    }
    $str.="</table>";
    echo "<table id='verificationPrix'>";
    echo $headStr;
    echo $str;
    echo "</table>";
    displayinhtml($table);*/
}
//include "502-trialfunctions.php";
if ($menu=='tickets'){
    echo "<h2>les tickets</h2>";
    //------------------------------------------------------------------
    //display CA per day
    // query cuts date > 2020-11-30
    $query="SELECT date(thedate) as date,month(thedate) as month,weekofyear(thedate) as wyear,weekday(thedate) as wday,prod_journal.* 
    FROM `prod_journal` WHERE thedate >'2020-11-30'  ORDER BY date ASC";

    $table=query_table($query);
    //displayTableInHtml($table);
    //------------------------------------------------------------------
    // table with date, weekday, day, nb, ca
    $monthQuery="SELECT max(month) as month,
    count(ticket) as nb,sum(amount) as ca from ($query) as A 
    group by month order by date";
    $table=query_table($monthQuery);

    // export XLS
    $filename="nb_et_ca_per_month";
    $str=exportToXls($filename.".xls",$table);
    echo "<a href='files/$filename.xls'>$filename.xls</a><br>";
    displayinhtml($table);  // display ca per month
    //------------------------------------------------------------------
    // table for all dates
    // with date, weekday, day, nb, ca
    $query="SELECT date,max(wyear) as wyear,max(wday) as wday,day(date) as day,
    count(ticket) as nb,sum(amount) as ca from ($query) as A 
    group by date order by date";
    $table=query_table($query);
    // export XLS
    $filename="nb_et_ca_per_day";
    $str=exportToXls($filename.".xls",$table);
    echo "<a href='files/$filename.xls'>$filename.xls</a><br>";
    
    displayinhtml($table);  // display ca per day

    
    $xlabels=[];
    $barData=[];
    $plotData=[];
    $init=1;
    foreach ($table as $key=>$row){
        if ($init==0){
            //dispArray($row);
            array_push($xlabels,$row['day']);
            array_push($barData,$row['nb']);
            array_push($plotData,$row['ca']);
            
        }
        $init=0;
        
    } 
    
    
       
    plot_chart_bar($xlabels,$barData,"Nombre de tickets","Nombre de passages en caisse par jour","ticketDaily.png");
    echo "<a href='files/ticketDaily.png'>Tickets par jour</a><br>";
    plot_chart_bar($xlabels,$plotData,"Chiffre d'affaire","Chiffre d'affaire par jour","caperday.png");
    echo "<a href='files/caperday.png'>Chiffre d'affaire par jour</a><br>";
    // ------------------------------------------------------------------
    // display ca per week
    $wquery="SELECT wyear,sum(nb) as nb, sum(ca) as ca,max(date) as date from ($query) as A where wyear<20 group by wyear order by wyear";
    $wtable=query_table($wquery);
    displayinhtml($wtable);  // display ca per day
    
    
    $xlabels=[];
    $barData=[];
    $plotData=[];
    $init=1;
    foreach ($wtable as $key=>$row){
        if ($init==0){
            //dispArray($row);
            array_push($xlabels,$row['wyear']);
            array_push($barData,$row['nb']);
            array_push($plotData,$row['ca']);
            
        }
        $init=0;
        
    }  
    // export XLS
    $filename="nb_et_ca_per_week";
    $str=exportToXls($filename.".xls",$table);
    echo "<a href='files/$filename.xls'>$filename.xls</a><br>";
    
      
    plot_chart_bar($xlabels,$barData,"Nombre de tickets","Nombre de passages en caisse par semaine","ticketWeekly.png");
    echo "<img class='figure' src='files/ticketWeekly.png'>Tickets par semaine</img><br>";
    echo "<a href='files/ticketWeekly.png'>Tickets par semaine</a><br>";
    plot_chart_bar($xlabels,$plotData,"Chiffre d'affaire","Chiffre d'affaire par semaine","caperweek.png");
    echo "<img class='figure' src='files/caperweek.png'>Chiffre d'affaire par semaine</a><br>";
    echo "<a href='files/caperweek.png'>Chiffre d'affaire par semaine</a><br>";
    
    //------------------------------------------------------------------
    // display nb, ca per weekday
    // average on specifi day of the week
    //$query="SELECT * from ($query) where weekday(thedate)";
    $query="SELECT wday,avg(nb) as nb,avg(ca) as ca from ($query) as A where wday in (3,4,5,6) group by wday  order by wday";
    $table=query_table($query,1);
    displayinhtml($table,1); // display ca per week days averaged
    
    //var_dump($table);
    $xlabels=[];
    $barData=[];
    $plotData=[];
    $init=1;
    foreach ($table as $key=>$row){
        if ($init==0){
            //dispArray($row);
            array_push($xlabels,dayoftheweek[$row['wday']]);
            array_push($barData,$row['nb']);
            array_push($plotData,$row['ca']);
            
        }
        $init=0;
        
    }   
    
    $filename="nb_et_ca_per_day";
    $str=exportToXls($filename.".xls",$table);
    echo "<a href='files/$filename.xls'>$filename.xls</a><br>";
     
    plot_chart_bar($xlabels,$barData,"Nombre de tickets","Moyenne du nombre de passages en caisse","avgticketDaily.png");
    echo "<img class='figure' src='files/avgticketDaily.png'>Tickets par jour</a><br><br>";
    echo "<a href='files/avgticketDaily.png'>Tickets par jour</a><br>";
    plot_chart_bar($xlabels,$plotData,"Chiffre d'affaire","Chiffre d'affaire moyen par jour","avgCAperday.png");
    echo "<img class='figure' src='files/avgCAperday.png'>CA par jour</a><br>";
    echo "<a href='files/avgCAperday.png'>CA par jour</a>";
}

// check stocks
if ($menu=='stocks'){
    $query="SELECT * from prod_stock where (ean,thedate) in (SELECT ean,max(thedate) as thedate FROM `prod_stock` group by ean) ORDER BY `prod_stock`.`stock` ASC";
    $query="select arti.ean,arti.designation,stock.stock,stock.thedate,stock.source from ($query) as stock left outer join prod_articles as arti on stock.ean=arti.ean where stock<0 order by stock asc;";
    $table=query_table($query);
    //echo "<a id='exportXls' href='./0017-stockExcel.php' onclick='exportXls();' target='_blank'>ExportExcel</a>";
    //include "../0021-functions/0506-exportToXls.php";
    $str=exportToXls("stockNegatif.xls",$table);
    echo "<a href='./files/stockNegatif.xls'>stockNegatif.xls</a>";
    echo $str;
    
    
}

// check ventes famille café depuis décembre
if ($menu=='cafe'){
    $query="SELECT plu.caht,STR_TO_DATE(concat(year(thedate),'-',month(thedate),'-1'),'%Y-%m-%d') as date FROM `prod_plu` as plu left outer join prod_articles as arti on plu.ean=arti.ean where arti.famille=3 and plu.thedate>='2020-12-01'";
    $query="SELECT date,sum(caht) as caht from ($query) as A group by date;";
    $table=query_table($query);
    displayinhtml($table);

}

// check ventes famille légumes depuis décembre
if ($menu=='legume'){
    $query="SELECT thedate,(week(thedate) +52*(year(thedate)-2020)) as week,plu.caht,plu.cattc FROM `prod_plu` as plu left outer join prod_articles as arti on plu.ean=arti.ean where arti.famille=28 and plu.thedate>='2020-12-01'";
    $query="SELECT min(thedate) as date,week,sum(caht) as caht,sum(cattc) as cattc from ($query) as A group by week;";
    $table=query_table($query);
    displayinhtml($table);

}


// check ventes serge belaiche par semaine depuis décembre
if ($menu=='serge'){
    $query="SELECT thedate,(week(thedate) +52*(year(thedate)-2021)) as week,plu.quantite,plu.caht,plu.cattc,arti.designation FROM `prod_plu` as plu left outer join prod_articles as arti on plu.ean=arti.ean where arti.fournisseur=40 and plu.thedate>='2020-12-01'";
    $query="SELECT min(thedate) as date,week,designation,sum(quantite) as quantite,sum(caht) as caht,sum(cattc) as cattc from ($query) as A group by designation,week order by week;";
    $table=query_table($query);
    displayinhtml($table);
}


// connexions
if ($menu=='connexions'){
    $query="SELECT A.*,U.prenom,U.nom FROM 
    (SELECT * FROM `prod_user_connexions` 
    WHERE user not in (6,58)) as A 
    LEFT OUTER JOIN prod_user as U 
    on A.user=U.id WHERE A.timestamp > CURDATE() - INTERVAL 2 WEEK";
    $table=query_table($query);
    displayinhtml($table);
}

// for all!
$str=exportToXls($menu.".xls",$table);
echo "<a href='files/$menu.xls'>$menu.xls</a>";
echo $str;
//displayinhtml($table);
echo 'sql pour rechercher par famille les ventes:SELECT * from (SELECT * FROM `prod_plu` as plu where thedate>="2021-02-01" and thedate<"2021-03-01") as plu left outer join (select * from prod_articles where departement=10) as art on plu.ean=art.ean';
echo "</body>
</html>";

?>
