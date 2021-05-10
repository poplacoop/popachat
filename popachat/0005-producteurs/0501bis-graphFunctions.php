<?php
//----------------------------------------------------------------------
// plot stock
function plot_chart_stocks_new($ean,$designation,$order){
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
    if(sizeof($table)>1){
        $stock=$table[1]['stock'];
        $stockDate=$table[1]['thedate'];
    
    
    
        $stockDelta=$stock-$lastStock;
        //echo $stockDico[$ean];
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
    }
    return $str;  
}
?>
