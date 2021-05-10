<?php
//----------------------------------------------------------------------
// retrieve stock information in a table
//
function getstockInformationDico($myRange,$ean="",$pluFilter=""){
    // function returns array for each ean and variables
    // 
    //a.ean,arti.designation,arti.fournisseur,
    //a.sales,a.stock,
    //arti.prixAchat*a.stock as valeurStock,
     //   (stock/sales*productAge/7) as semainedestock,
     // sales-stock as commande1mois,
     //   2*sales-stock as commande2mois,
     //3*sales-stock as commande3mois,
     //minDate
    
    
    
    $file="./files/json".$myRange.date("Y-m-d").".json";
    if ((file_exists($file))&&($_SESSION['myRange']==$myRange)&&0){
        echo "utilisation du fichier enregistré";
        $table = json_decode(file_get_contents($file),TRUE);
    }
    else{
        $data=[];
        //echo "Calculs de la page";
        $whereean="";
        if ($ean!=""){$whereean.=" and ean=$ean";}
        
        // this function computes commandes: semaines de stock, commandes nécessaires
        //---------------------------------------------------------------------------
        // the source
        //$str="<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js'></script>";
        $str=""; // start string
        
        $maxDay=$myRange*7; // defines depth to look for
        $initDate=time()-$maxDay*24*3600; // init date
        //echo "die 1";die;
        
        //--------------------------------------------------------------
        // retrieve articles
        $query="SELECT * FROM prod_articles where 1=1 $whereean $pluFilter";
        //echo $query;
        $articleTable=query_table($query,0);
        
        foreach ($articleTable as $row){
            $ean=$row['ean'];
            $data[$row['ean']]=[];
            $data[$row['ean']]['designation']=$row['designation'];
            $data[$row['ean']]['prixAchat']=$row['prixAchat'];  
        }
        
        //var_dump($data['3489940036114']);
        //------------------------------------------------------------------
        // get sales on the period:
        //
        
        $salesQuery="select ean,sum(quantite) sales from prod_plu where thedate > current_date- interval ".$myRange." week $whereean group by ean";
        $salesQuery="select * from ($salesQuery) as A where sales>1";
        //echo $salesQuery;
        $salesTable=query_table($salesQuery,0);
        //displayinhtml($salesTable);
        foreach ($salesTable as $row){
            $ean=$row['ean'];
            if (isset($data[$row['ean']])){
                $data[$ean]['sales']=$row['sales'];
            }
        }
        echo "<br>";
        //dispArray($data['3770010033165']);
        //echo "die 2";die;
        //----------------------------------------------------------------------
        // retrieve today stock  
        //
        $stockquery="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
                FROM `prod_stock` where not isnull(stock) $whereean group by ean) as last 
                left outer join (SELECT * FROM prod_stock WHERE 1=1 $whereean) as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
                ";
        $stockquery="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate
                FROM `prod_stock` where not isnull(stock) $whereean group by ean) as last 
                left outer join (SELECT * FROM prod_stock WHERE 1=1 $whereean) as stock on last.ean=stock.ean and last.thedate=stock.thedate
                ";
        //$stockquery="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
        //        FROM `prod_stock` where not isnull(stock) group by ean) as last 
        //        left outer join (SELECT * FROM prod_stock WHERE 1=1 $whereean) as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
         //       where 1= 1 $whereean ORDER BY `last`.`thedate` ASC ";
        //echo $stockquery;
        $stockTable=query_table($stockquery,0);
        foreach ($stockTable as $row){
            $ean=$row['ean'];
            if (isset($data[$row['ean']])){
                $data[$row['ean']]['stock']=$row['stock'];
                //if (!isset($data[$ean]['prixAchat'])){echo "<br>".$ean;dispArray($data[$ean]);}
                $data[$row['ean']]['valeurStock']=$data[$ean]['prixAchat']*$data[$ean]['stock'];
            }
            
        }
        //displayinhtml($stocktable);
        //echo "die 3";die;
        //$query="SELECT stock.ean,sales.sales,stock.stock FROM ($stockquery) as stock LEFT OUTER JOIN ($salesquery) as sales   on sales.ean=stock.ean";
        
        //$table=query_table($query);
        //displayinhtml($table);
        //echo "die 4";die;
        //--------------------------------------------------------------
        // retrieve first introduction day
        $queryIntroDate="SELECT ean,min(thedate) as minDate,datediff(current_date,min(thedate)) as productAge FROM prod_stock_mvt WHERE LEFT(bl,2)='BL' $whereean GROUP BY ean";
        $queryIntroDate="SELECT ean,minDate,if(productAge>".(7*$myRange).",".(7*$myRange).",productAge) as productAge FROM ($queryIntroDate) as A";
        $introDateTable=query_table($queryIntroDate,0);
        //displayinhtml($introDateTable);
        foreach ($introDateTable as $row){
            $ean=$row['ean'];
            if (isset($data[$row['ean']])){
                $data[$row['ean']]['introDate']=$row['minDate'];
                $data[$row['ean']]['productAge']=$row['productAge'];
                if (isset($data[$row['ean']]['sales'])){
                    $weightedSales=$data[$ean]['sales'];
                    $weightedSales=$data[$ean]['sales']*$maxDay/$data[$ean]['productAge'];
                    $data[$ean]['semainedestock']=$data[$ean]['stock']/$weightedSales;
                    $data[$ean]['commande1mois']=$weightedSales-$data[$ean]['stock'];
                    $data[$ean]['commande2mois']=2*$weightedSales-$data[$ean]['stock'];
                    $data[$ean]['commande3mois']=2*$weightedSales-$data[$ean]['stock'];
                }
            }
        }
        
        
        
        //a.ean,arti.designation,arti.fournisseur,
    //a.sales,a.stock,
    //arti.prixAchat*a.stock as valeurStock,
     //   (stock/sales*productAge/7) as semainedestock,
     // sales-stock as commande1mois,
     //   2*sales-stock as commande2mois,
     //3*sales-stock as commande3mois,
     //minDate
        
        
        
        
        
        // new from stock
        //$queryIntroDate="SELECT ean,min(thedate) as thedate,max(id) as id FROM `prod_stock` where not isnull(stock) group by ean";
        //echo $queryIntroDate;
       
        //$query="SELECT A.*,if(isnull(B.minDate),'2021-01-01',B.minDate) as minDate,if(isnull(B.productAge),".(7*$myRange).",productAge)  as productAge FROM ($query) as A 
        //left outer join ($queryIntroDate) as B on A.ean=B.ean";
        //echo $query;
        //$table=query_table($query);
        //displayinhtml($table);
        //echo "<br>die 5";die;
        /*$newquery="SELECT a.ean,arti.designation,arti.fournisseur,a.sales,a.stock,arti.prixAchat*a.stock as valeurStock,
        (stock/sales*productAge/7) as semainedestock,sales-stock as commande1mois,
        2*sales-stock as commande2mois,3*sales-stock as commande3mois,minDate from ($query) as a 
        left outer join 
        (SELECT * FROM prod_articles where 1=1 $whereean $pluFilter) as arti 
        on arti.ean=a.ean 
        order by fournisseur, semainedestock desc";
        */
        
        //$newquery="SELECT ean,sales,stock as commande from ($query) as a";
        //echo $newquery;
        //$table=query_table($newquery);
        //dispArray($data['3770010033165']);
        //file_put_contents($file, json_encode($table));
        $_SESSION['myRange']=$myRange;
        //echo $file;
    }
    return $data;
    
}
require_once "../0021-functions/0506-exportToXls.php";
//----------------------------------------------------------------------
// retrieve stock information in a dico
//
/*
function getstockInformationDico($myRange,$ean=""){
    $table=getstockInformation($myRange,$ean);
    $dico=[];
    $keys=$table[0];
    //dispArray($keys);
    $init=1;
    foreach ($table as $row){
        if ($init==1){$init=0;}else{
            $theRow=[];
            //dispArray($row);
            foreach($keys as $idx=>$key){
                
                $theRow[$key]=$row[$key];
            }
            array_shift($theRow);
            $dico[$row['ean']]=$theRow;
            echo "<br>";
            dispArray($theRow);
            echo "<br><br>";
        }
    }
    return $dico;
    
}*/

//----------------------------------------------------------------------
// plot stock outdated
//
function plot_chart_stocks_newk($ean,$designation,$order){
    // need to number graph if not unique
    if (!isset($_SESSION['graphNb'])){$_SESSION['graphNb']=0;}
    $graphId=$_SESSION['graphNb']+1;
    $_SESSION['graphNb']=$graphId;
    
    // the source
    //$str="<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js'></script>";
    $str=""; // start string
    
    $maxDay=$order['myRange']*7; // defines depth to look for
    $initDate=time()-$maxDay*24*3600; // init date
    
    //get InitStock
    // get stock before date: first the maximum date 
    $sql="SELECT max(thedate) as thedate from prod_stock_mvt where ean='$ean' and thedate < current_date- interval ".$order['myRange']." week group by ean";
    $table=query_table($sql,1);
    //displayinhtml($table);
    if (sizeof($table)==1){// no stock found: back calculate
        $initStock=0;
    }
    else{
        $stockDate=$table[1]['thedate'];  // initial date
        // get Stock at the date
        $sql="SELECT thedate,ajout,oldStock,bl from prod_stock_mvt where ean='$ean' and thedate='$stockDate'";
        $table=query_table($sql,1);
        $ajout=$table[1]['ajout'];
        $oldStock=$table[1]['oldStock'];
        $stockType=$table[1]['bl'];
        //displayinhtml($table);
        // get sales till date: the date is not included (stock for next morning)
        $query="select sum(quantite) as sales from prod_plu where ean='$ean' and thedate >'".date("Y-m-d",$initDate)."' and thedate < current_date- interval ".$order['myRange']." week";
        $table=query_table($query);
        //echo $query;
        $sales=$table[1]['sales'];
        //displayinhtml($table);
        //compute initStock= stock - sales
        
        if(substr($stockType,0,1)=="V"){ // inventaire
            $initStock=$ajout-$sales;
        }
        if(substr($stockType,0,2)=="BL"){
            $initStock=$oldStock+$ajout-$sales; // livraison
        }
        
        
    }
    //echo ("stock initial".$initStock);
    //------------------------------------------------------------------
    // get sales on the period:
    $query="select thedate,quantite from prod_plu where ean='$ean' and thedate > current_date- interval ".$order['myRange']." week";
    //display_query($query);
    $salesTableDico=query_table_dico($query);
    $sales=[];
    
    foreach ($salesTableDico as $row){
        $sales[$row['thedate']]=$row['quantite'];
    }
    //dispArray($sales);
    //------------------------------------------------------------------
    // get stock mvt on the period
    //
    $sql="SELECT * from prod_stock_mvt where ean='$ean' and thedate > current_date- interval ".$order['myRange']." week order by thedate";
    //display_query($sql);
    $stockMvtTableDico=query_table_dico($sql);
    /*foreach ($stockMvtTableDico as $row){
        dispArray($row);
    }*/
    $stockMvt=[];
    $stockType=[];
    foreach ($stockMvtTableDico as $row){
        $stockFinal[$row['thedate']]=$row['ajout'];
        $stockType[$row['thedate']]=$row['bl'];
        $ajustStock[$row['thedate']]=$row['ajout']-$row['oldStock'];
    }
    //dispArray($stockType);
    //------------------------------------------------------------------
    // start filling values
    $data=[];
    
    $data['x'][0]=date("Y-m-d",$initDate);
    $data['stock'][0]=$initStock;
    $curDate=date("Y-m-d",$initDate);
    $data['sales'][0]=0;
    $data['livraisons'][0]=0;
    $data['adjust'][0]=0;
    $data['inventaire'][0]='NaN';
    if (isset($sales[$curDate])){$data['sales'][0]=$sales[$curDate];};// days when sale exist
    //echo $data['stock'][0];
    for ($day=1;$day<$maxDay;$day++){
        $curDate=date("Y-m-d",$initDate+$day*24*3600);
        //echo $curDate."<br>";
        $data['x'][$day]=$curDate;
        $data['stock'][$day]=$data['stock'][$day-1];
        $data['sales'][$day]=0;
        $data['adjust'][$day]=0;
        $data['inventaire'][$day]='NaN';
        $data['livraisons'][$day]='NaN';
        //echo $data['stock'][$day];
        if (isset($sales[$curDate])){
            $data['stock'][$day]=$data['stock'][$day]-$sales[$curDate];
            $data['sales'][$day]=$sales[$curDate];
        }
        //echo $data['stock'][$day];
        if (isset($stockFinal[$curDate])){
            //echo $curDate;
            if(substr($stockType[$curDate],0,1)=="V"){
                //echo "<br>Inventaire<br>";
                //echo $stockFinal[$curDate]."<br>";
                $data['stock'][$day]=0*$data['stock'][$day]+$stockFinal[$curDate];
                //echo $data['stock'][$day];
                $data['adjust'][$day]=$ajustStock[$curDate];
                $data['inventaire'][$day]=$stockFinal[$curDate];
                
            }
            if(substr($stockType[$curDate],0,2)=="BL"){
                $data['stock'][$day]=$data['stock'][$day]+$stockFinal[$curDate];
                $data['livraisons'][$day]=$stockFinal[$curDate];
            }
            
            
        }
        //echo $data['inventaire'][$day];
        $lastStock=$data['stock'][$day];
        //echo $data['stock'][$day];
        //echo "<br>";
    }
    //------------------------------------------------------------------
    // correct data for true stock
    //----------------------------------------------------------------------
    // retrieve stock
    $query="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
            FROM `prod_stock` where not isnull(stock) group by ean) as last 
            left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
            where last.ean=".$order["ean"]." ORDER BY `last`.`thedate` ASC ";
    $table=query_table($query);
    $stock=$table[1]['stock'];
    $stockDate=$table[1]['thedate'];
    
    $stockDelta=$stock-$lastStock;
    //echo "$stockDelta=".$stockDico[$ean];
    //var_dump($data['inventaire']);
    //
        // prepare values for javascript script
        $stock=[];
        $xstr="[";
        $stockstr="[";
        $adjustStockStr="[";
        $inventairestr="[";
        $salesstr="[";
        $salesstr="[";
        $livraisonsStr="[";
        $stockcomma="";
        $background='[';
        for ($k=0;$k<$maxDay;$k++){
            array_push($stock,floatval($data['stock'][$k]));
            $xstr.=$stockcomma."'".convert_date($data['x'][$k],0,3,1)."'";
            $stockstr.=$stockcomma."'".number_format(floatval($data['stock'][$k])+$stockDelta,2)."'";
            if ($data['inventaire'][$k]!="NaN"){
                $inventairestr.=$stockcomma."'".number_format(floatval($data['inventaire'][$k]),2)."'";
            }
            else{
                $inventairestr.=$stockcomma."'".$data['inventaire'][$k]."'";
            }
            $livraisonsStr.=$stockcomma."'".number_format(floatval($data['livraisons'][$k]),2)."'";
            
            $salesstr.=$stockcomma."'".number_format(floatval(-$data['sales'][$k]),2)."'";
            $adjustStockStr.=$stockcomma."'".number_format(floatval($data['adjust'][$k]),2)."'";
            $stockcomma=",";
            $background.=$stockcomma."'blue'";
        }
        $xstr.="]";
        $stockstr.="]";
        $inventairestr.="]";
        $livraisonsStr.="]";
        $salesstr.="]";
        $adjustStockStr.="]";
        $background.="]";
        $title=addslashes("Stock de $designation");
        //var_dump($livraisonsStr);
        $str.="<div class='graphContainer' ><canvas id='$graphId' ></canvas></div>";
        $str.="<script>
            var chartData={
                labels: $xstr,
                datasets:[{
                    type: 'bar',
                    label: 'ventes',
                    backgroundColor: $background,
                    data:$salesstr
                },
                {
                    type: 'bar',
                    label: 'livraisons',
                    backgroundColor: 'green',
                    data:$livraisonsStr,
                },
                {
                    type: 'bar',
                    label: 'adjust',
                    backgroundColor: 'red',
                    data:$adjustStockStr
                },
                {
                    type: 'line',
                    label: 'inventaire',
                    backgroundColor: 'red',
                    borderColor:'red',
                    showLine:0,
                    pointRadius:8,
                    fill:false,
                    data:$inventairestr
                },
                {
                    type: 'line',
                    label: 'stock',
                    backgroundColor: 'black',
                    borderColor:'black',
                    fill:false,
                    data:$stockstr
                }]
            };
            
            new Chart(document.getElementById('$graphId'), {
                type: 'bar',
                data:chartData,
                options: {
                    legend: { display: false },
                    title: {
                    display: true,
                    text: '$title'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }

            }


        });

            </script>";
    return $str;  
}
//-------------------------------------------------------------------------
// display disk chart
//
function display_disk_chart($data){
    
    
}


?>
