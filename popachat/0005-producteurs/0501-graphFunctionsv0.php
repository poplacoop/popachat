<?php 
// javascript
function plot_chart_sales($ean,$designation,$order){
    if (!isset($_SESSION['graphNb'])){$_SESSION['graphNb']=0;}
    $graphId=$_SESSION['graphNb']+1;
    $_SESSION['graphNb']=$graphId;
    //$str="<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js'></script>";
    $str="";
    
    $maxDay=$order['myRange']*7; // defines depth to look for
    //------------------------------------------------------------------
    // get last stock and date
    $query="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
            FROM `prod_stock` where not isnull(stock) group by ean) as last 
            left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
            ORDER BY `last`.`thedate` ASC ";
    $stockDico=create_one_field_dictionnary_sql($query,'ean','stock');
    //$stockDelta=$stockDico[$ean]-$lastStock;
    $stockDico=create_one_field_dictionnary_sql($query,'ean','thedate');
    $stockDate=$stockDico[$ean];
    
    
    
    //------------------------------------------------------------------
    // get information from plu
    $sql="SELECT thedate,$maxDay+1-DATEDIFF(NOW(),date(thedate)) as daysfromnow,format(quantite,2) as quantite 
    FROM prod_plu WHERE ean=$ean and  thedate > current_date- interval ".$order['myRange']." week order by thedate";
    //echo $sql;
    $table=query_table($sql,1);
    
    if(sizeof($table)>1){ // if some data available
        $data=[];
        $data['y']=[];
        $data['x']=[];
        // retrieve data from the table
        for ($k=1;$k<sizeof($table);$k++){
            $refK=$table[$k]['daysfromnow'];                    // gets numbers of days from now
            $data['y'][$refK]=$table[$k]['quantite'];           // define y: quantite
            $data['x'][$refK]=strtotime($table[$k]['thedate']); // define x: date
        }
        $refDate=$data['x'][$refK];                         // defines the refdate (last date of the table)
        // $refK is the 
        // fills in the gap (dates where there is no data)
        //
        for ($k=0;$k<$maxDay+2;$k++){  // goes through all the days from 0 to oldest day + 2 
            //echo $d;
            if(!isset($data['y'][$k])){
                $data['y'][$k]=0;
                $data['x'][$k]=date($refDate+1*($k-$refK)*24*3600);
            }

        }
        // prepare values for javascript script
        $y=[];
        $xstr="[";
        $ystr="[";
        $ycomma="";
        $background='[';
        for ($k=0;$k<$maxDay+2;$k++){
            array_push($y,floatval($data['y'][$k]));
            $ystr.=$ycomma."'".number_format(floatval($data['y'][$k]),2)."'";
            $xstr.=$ycomma."'".convert_date(date("Y-m-d",$data['x'][$k]),0,3,1)."'";
            $ycomma=",";
            $background.=$ycomma."'blue'";
        }
        $xstr.="]";
        $ystr.="]";
        $background.="]";
        $title=addslashes("Quantite vendue par jour de $designation");

        $str.="<div><canvas id='$graphId' width='50%' height='50'></canvas></div>";
        $str.="<script>
              
        new Chart(document.getElementById('$graphId'), {
            type: 'bar',
            data: {
              labels: $xstr,
              datasets: [
                {
                  label: '$title',
                  backgroundColor: $background,
                  data: $ystr
                }
              ]
            },
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
    else{
        $str.= "pas de vente sur la période";
    }
    return $str;  
}

//----------------------------------------------------------------------
// plot stock
function plot_chart_stocks($ean,$designation,$order){
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
    
    // start filling values
    $data=[];
    
    $data['x'][0]=date("Y-m-d",$initDate);
    $data['y'][0]=$initStock;
    $curDate=date("Y-m-d",$initDate);
    $data['sales'][0]=0;
    $data['adjust'][0]=0;
    if (isset($sales[$curDate])){$data['sales'][0]=$sales[$curDate];};
    //echo $data['y'][0];
    for ($day=1;$day<$maxDay;$day++){
        $curDate=date("Y-m-d",$initDate+$day*24*3600);
        //echo $curDate."<br>";
        $data['x'][$day]=$curDate;
        $data['y'][$day]=$data['y'][$day-1];
        $data['sales'][$day]=0;
        $data['adjust'][$day]=0;
        //echo $data['y'][$day];
        if (isset($sales[$curDate])){
            $data['y'][$day]=$data['y'][$day]-$sales[$curDate];
            $data['sales'][$day]=$sales[$curDate];
        }
        //echo $data['y'][$day];
        if (isset($stockFinal[$curDate])){
            //echo $curDate;
            if(substr($stockType[$curDate],0,1)=="V"){
                //echo "<br>Inventaire<br>";
                //echo $stockFinal[$curDate]."<br>";
                $data['y'][$day]=0*$data['y'][$day]+$stockFinal[$curDate];
                //echo $data['y'][$day];
                $data['adjust'][$day]=$ajustStock[$curDate];
            }
            if(substr($stockType[$curDate],0,2)=="BL"){
                $data['y'][$day]=$data['y'][$day]+$stockFinal[$curDate];
            }
            
        }
        $lastStock=$data['y'][$day];
        //echo $data['y'][$day];
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
    //echo $stockDico[$ean];
    
    //
        // prepare values for javascript script
        $y=[];
        $xstr="[";
        $ystr="[";
        $salesstr="[";
        $adjustStockStr="[";
        $ycomma="";
        $background='[';
        for ($k=0;$k<$maxDay;$k++){
            array_push($y,floatval($data['y'][$k]));
            
            $xstr.=$ycomma."'".convert_date($data['x'][$k],0,3,1)."'";
            $ystr.=$ycomma."'".number_format(floatval($data['y'][$k])+$stockDelta,2)."'";
            $salesstr.=$ycomma."'".number_format(floatval($data['sales'][$k]),2)."'";
            $adjustStockStr.=$ycomma."'".number_format(floatval($data['adjust'][$k]),2)."'";
            $ycomma=",";
            $background.=$ycomma."'blue'";
        }
        $xstr.="]";
        $ystr.="]";
        $salesstr.="]";
        $adjustStockStr.="]";
        $background.="]";
        $title=addslashes("Stock de $designation");

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
                    label: 'adjust',
                    backgroundColor: 'red',
                    data:$adjustStockStr
                },
                {
                    type: 'line',
                    label: 'stock',
                    backgroundColor: 'black',
                    borderColor:'black',
                    fill:false,
                    data:$ystr
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
//----------------------------------------------------------
// in php
function plot_chart_bar($xlabels,$barData,$yTitle,$title,$filename){
    //phpinfo();
     // content="text/plain; charset=utf-8"
    require_once ('../0020-classes/jpgraph-4.3.4/src/jpgraph.php');
    require_once ('../0020-classes/jpgraph-4.3.4/src/jpgraph_bar.php');
    require_once ('../0020-classes/jpgraph-4.3.4/src/jpgraph_line.php');

    $theme = isset($_GET['theme']) ? $_GET['theme'] : null;

    // Create the graph. These two calls are always required
    $graph = new Graph(600,300);    

    $graph->SetScale("textlin");
    if ($theme) {
        $graph->SetTheme(new $theme());
    }
    $theme_class = new AquaTheme;
    $graph->SetTheme($theme_class);


    $top = 60;
    $bottom = 30;
    $left = 80;
    $right = 30;
    //$graph->Set90AndMargin($left,$right,$top,$bottom);  // rotation

    //$plot = array();
    // Create the bar plots
    //dispArray($barData);
    $plot=new BarPlot($barData);

    $graph->xaxis->SetTickLabels($xlabels);
    //$plot[1] = new LinePlot($lineData);
    $graph->Add($plot);


    //$title = mb_convert_encoding($title,'UTF-8');
    $graph->title->Set($title);
    //$graph->xaxis->title->Set("X-title");
    //$graph->yaxis->title->Set($yTitle);

    // Display the graph

    //$graph->Stroke();
    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $fileName = "./files/".$filename;
    $graph->img->Stream($fileName);
}

?>
