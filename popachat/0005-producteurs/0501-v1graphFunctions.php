<?php
@session_start();
//require_once "0000-initFilesProd.php";
require_once "0500-listFunctions.php";
//include "0501-graphFunctions.php";
//require_once "0503-stockFunctions.php";
//----------------------------------------------------------------------
//  returns a table with data for each ean
//
function getStockValue($order){
    // finds the days when there is a sale
    $query="SELECT thedate,sum(cattc) as cattc FROM `prod_plu` where thedate > curdate()-interval ".(7*$order['myRange'])." day group by thedate ORDER BY sum(cattc) ASC ";
    $listeActiveDays="SELECT thedate from ($query) as A where cattc >1000";
    $query="SELECT * FROM prod_plu WHERE thedate in ($query);";

    //----------------------------------------------------------------------
    // Stock evolution
    //$order['myRange']=4; // defines depth to look for
    $ean=$order['ean'];
    $maxDay=$order['myRange']*7; // defines depth to look for
    //------------------------------------------------------------------
    //get InitStock
    //------------------------------------------------------------------
    // get stock before date: first the maximum date  in stock mvt
    $sql="SELECT max(thedate) as thedate from prod_stock_mvt where ean='$ean' and SUBSTR(bl,1,1)='V' and thedate < current_date- interval ".$order['myRange']." week group by ean";
    $table=query_table($sql,1);
    //displayinhtml($table);
    //displayinhtml($table);
    if (sizeof($table)>1){
        $initDate=$table[1]['thedate'];
        //echo $initDate;
        // init stock is defined by inventory but we do not know if it is before sale or after sale
        // assume before.
        $sql="SELECT ean,thedate,ajout from prod_stock_mvt where ean='$ean' and SUBSTR(bl,1,1)='V' and thedate='".$initDate."'";
        //echo $sql;
        $table=query_table($sql,1);
        //displayinhtml($table);
        $initStock=$table[1]['ajout'];
        $initDate=date("Y-m-d",strtotime($table[1]['thedate'])-24*3600);
        $noInitialStock=0;  
    }
    else{
        $sql="SELECT min(thedate) as thedate from prod_stock_mvt where ean='$ean' ";
        $table=query_table($sql,1);
        //displayinhtml($table);
        $initStock=0; 
        $initDate=date("Y-m-d",strtotime($table[1]['thedate'])-24*3600);
        $noInitialStock=1; 
        
    }
    //echo $initDate." === ".$initStock."<br>";
    $initTime=strtotime($initDate);  // initial date
    //echo $initTime;
    // first stock found
    //$initStock is fixed except when there is none...
    //echo ("stock initial".$initStock);
    
    //------------------------------------------------------------------
    // get sales on the period: query starting from $initDate
    //------------------------------------------------------------------
    $query="select thedate,quantite,stock from prod_plu where ean='$ean' and thedate >= '$initDate'";
    //display_query($query);
    $salesTableDico=query_table_dico($query);
    $data['sales']=[];
    foreach ($salesTableDico as $row){
        $data['sales'][$row['thedate']]=$row['quantite'];
    }
    //dispArray($data['sales']);
    //------------------------------------------------------------------
    // get stock mvt on the period
    //------------------------------------------------------------------
    // inventaire is at beggining of day
    // realstock at the end
    
    
    
    $sql="SELECT * from prod_stock_mvt where ean='$ean' and thedate >= '$initDate' order by thedate";
    //echo $sql;
    //display_query($sql);
    $stockMvtTableDico=query_table_dico($sql);
    //var_dump($stockMvtTableDico);
    /*foreach ($stockMvtTableDico as $row){
        dispArray($row);
    }*/
    $data['oldStock']=[];
    $data['type']=[];
    $data['bl']=[];
    $data['diffStock']=[];
    $data['inventaire']=[];
    $data['livraison']=[];
    foreach ($stockMvtTableDico as $row){
        //echo $row['thedate']." raison ".$row['raison']."bl ".$row['bl']."oldStock ".$row["oldStock"]." ajout ".$row["ajout"]." <br>";
        $curDate=$row['thedate'];
        $prevDate=date("Y-m-d",strtotime($curDate)-3600*24);
        //dispArray($row);
        $data['oldStock'][$row['thedate']]=$row['oldStock'];
        
        $reason=$row['raison'];
        $stockType="";
        //if ($reason!="MANQUANT"){
            $data['bl'][$row['thedate']]=$row['bl'];
            $stockType=$data['bl'][$row['thedate']];
            $data['ajout'][$row['thedate']]=$row['ajout'];
        //}
        if (!isset($data['diffStock'][$row['thedate']])){$data['diffStock'][$row['thedate']]=0;}
        if (!isset($data['livraison'][$row['thedate']])){$data['livraison'][$row['thedate']]=0;}
        if (isset($data['sales'][$curDate])){$sales=$data['sales'][$curDate];}else{$sales=0;}
        $data['realStock'][$prevDate]=$data['oldStock'][$curDate]; // assume that all stock movements are at the beginning of day
        if(substr($stockType,0,1)=="V"){ // inventaire problème si deux inventaires....
            //$data['diffStock'][$row['thedate']]+=$data['ajout'][$row['thedate']]-$data['oldStock'][$row['thedate']]; //absolute value given assume stock before.
            //echo "divstock".$data['diffStock'][$row['thedate']]." stock is ".$data['ajout'][$row['thedate']];
            //$data['realStock'][$prevDate]=$data['ajout'][$curDate];
            $data['inventaire'][$curDate]=$data['ajout'][$curDate];
            //echo "<br>Inventaire".$curDate."=".$data['inventaire'][$curDate]."=ajout".$data['ajout'][$curDate]."=old stock".$data['oldStock'][$curDate]."<br>";
        }
        
        if(substr($stockType,0,3)=="POP"){ // inventaire problème si deux inventaires....
            //$data['diffStock'][$row['thedate']]+=$data['ajout'][$row['thedate']]-$data['oldStock'][$row['thedate']]; //absolute value given assume stock before.
            //echo "divstock".$data['diffStock'][$row['thedate']]." stock is ".$data['ajout'][$row['thedate']];
            //$data['realStock'][$prevDate]=$data['ajout'][$curDate];
            $data['inventairePop'][$curDate]=$data['ajout'][$curDate];
            //echo "<br>Inventaire".$curDate."=".$data['inventaire'][$curDate]."=ajout".$data['ajout'][$curDate]."=old stock".$data['oldStock'][$curDate]."<br>";
        }
        
        if((substr($stockType,0,2)=="BL")&&($reason!="MANQUANT")){// livraison
            $data['diffStock'][$row['thedate']]+=$data['ajout'][$row['thedate']]; //absolute value given assume stock before.
            $data['livraison'][$curDate]+=$data['ajout'][$row['thedate']];
            //$data['inventaire'][$curDate]=$data['oldStock'][$curDate]+$data['ajout'][$curDate];
            //echo "livraison";
        }
        if(substr($stockType,0,1)=="S"){// sortie
            $data['diffStock'][$row['thedate']]+=-$data['ajout'][$row['thedate']]; //absolute value given assume stock before.
            //$data['inventaire'][$curDate]=$data['oldStock'][$curDate]-$data['ajout'][$curDate];
            //echo "sortie";
        }
        if(substr($stockType,0,1)=="E"){// entrée
            //echo "entrée";
            $data['diffStock'][$row['thedate']]+=$data['ajout'][$row['thedate']]; //absolute value given assume stock before.           
            //$data['inventaire'][$curDate]=$data['oldStock'][$curDate]+$data['ajout'][$curDate];
        }
        //echo "<br>Final diff stock$curDate =".$data['diffStock'][$curDate]."<br>";
    }
    // clean inventaire
    for ($day=1;$day<=$maxDay;$day++){
        if (isset($data['inventaire'][$day])){
            if ((isset($data['livraison'][$day]))||(isset($data['sales'][$day]))){
                unset($data[$day]);
            }
        }
        
        
    }
    //$curDate="2021-03-04";
    //echo "<br>Sortie Boucle stock$curDate =".$data['diffStock'][$curDate]."<br>";
    //------------------------------------------------------------------
    // get stock from prod_stock
    //$query="select ean,thedate,stock,source from prod_stock where ean='$ean' and thedate >= '$initDate'";
    //$stockTableDico=query_table_dico($query);
    //foreach ($stockTableDico as $row){
        //$data['realstock'][$row['thedate']]=$row['stock'];
    //}
    //echo "inventairePop";
    //var_dump($data['inventairePop']);
    //------------------------------------------------------------------
    // Start loop on $day
    // construct Stock function
    //------------------------------------------------------------------
    $data['stock']=[];
    $data['stock'][$initDate]=$initStock;
    $maxDay=floor((time()-$initTime)/24/3600);
    //echo "$<br>maxDay$maxDay<br>";
    $lastInventaireDate=date("Y-m-d",0);
    //echo $lastInventaireDate;
    for ($day=1;$day<=$maxDay;$day++){
        $curDate=date("Y-m-d",$initTime+$day*24*3600);
        $prevDate=date("Y-m-d",$initTime+($day-1)*24*3600);
        $data['stock'][$curDate]=$data['stock'][$prevDate];
        //echo "<br>Beginning:$curDate Stock=".$data['stock'][$curDate];
        //echo "<br>days=$day $curDate =".$data['diffStock'][$curDate]."<br>";
        //echo "<br>start:".$curDate."<br>";
        $filter=['ajout','sales','oldStock','stock','adjust'];
        foreach ($filter as $key){
            if (!isset($data[$key][$curDate])){
                $data[$key][$curDate]=0;
            }
        }
        // assume inventaire made at beggining correct  -- adjust for bug.
        // make sure only action in the day
        if (isset($data['inventaire'][$curDate]))
        {
            $data['diffStock'][$curDate]+=($data['inventaire'][$curDate])-$data['stock'][$curDate];
            $data['stock'][$curDate]=$data['inventaire'][$curDate];
            //echo $lastInventaireDate.">".$curDate;
            if ($lastInventaireDate<$curDate){$lastInventaireDate=$curDate;}
            
        }
        //echo "<br>Après inventaire:$curDate Stock=".$data['stock'][$curDate];
        if (isset($data['sales'][$curDate])){
            $data['stock'][$curDate]+=-$data['sales'][$curDate];
            $data['sales'][$curDate]=-$data['sales'][$curDate];
            
        }
        //echo "<br>Après ventes:$curDate Stock=".$data['stock'][$curDate];
        if (isset($data['bl'][$curDate])){
            //echo $curDate." = ";
            //echo "<br>BL $curDate bl".$data['bl'][$curDate];
            //echo " oldStock ".$data['oldStock'][$curDate];
            //if (isset($data['inventaire'][$curDate])){echo " =Inv= ".$data['inventaire'][$curDate];}
            //echo " =diffStock= ".$data['diffStock'][$curDate];
            //echo "=sales=".$data['sales'][$curDate]."<br>";
            
            $data['stock'][$curDate]+=$data['diffStock'][$curDate]; //absolute value given assume stock before.    
            
            //if (isset($data['livraison'])){$data['diffStock'][$curDate]=0;}
            $data['diffStock'][$curDate]-=$data['livraison'][$curDate]; // erase diff stock 
            
        }
        //echo "<br>Before:$curDate Stock=".$data['stock'][$curDate];
        // assumes inventairePop is after the day.
        if (isset($data['inventairePop'][$curDate])){
            $data['diffStock'][$curDate]+=($data['inventairePop'][$curDate])-$data['stock'][$curDate];
            $data['stock'][$curDate]=$data['inventairePop'][$curDate];
            //echo "lastInventaire ".$lastInventaireDate.">".$curDate."<br>";
            if ($lastInventaireDate<$curDate){$lastInventaireDate=$curDate;}
        }

    }
    
    //echo "last inventaire".$lastInventaireDate;
    //var_dump($data['diffStock']);
    //--------------------------------------------------------------
    // correct data for true stock
    //----------------------------------------------------------------------
    // retrieve stock
    // correction de stock
    if (1==0){
        $query="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
                FROM `prod_stock` where not isnull(stock) group by ean) as last 
                left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
                where last.ean=".$order["ean"]." ORDER BY `last`.`thedate` ASC ";
        $table=query_table($query);
        $stock=$table[1]['stock'];
        $stockDate=$table[1]['thedate'];
        
        $stockDelta=$stock-$data['stock'][$curDate];
        //echo "<br>stock=".$stock;
        //echo "<br>Stock Delta $stockDelta<br>";
        foreach ($data['stock'] as $idx=>$key){
            //echo "<br>".$idx." ---  ".$data['stock'][$idx]." correction ";
            // correction seulement depuis last inventaire.
            
            //if ($idx>$lastInventaireDate){
            if ($idx>=$curDate){
                $data['stock'][$idx]=$data['stock'][$idx]+$stockDelta;
                if (isset($data['diffStock'][$idx])){$data['diffStock'][$idx]-=$stockDelta;}
            }
            //echo $idx." ---  ".$data['stock'][$idx]."<br>";
        }
    }
    // fin de correction de stock
    $keys=['stock','sales','inventaire','inventairePop','livraison','diffStock'];
    $size=sizeof($data['stock']);
    $maxDay=$order['myRange']*7;
    
    $initDate=date("Y-m-d",time()-3600*24*7*$order['myRange']);
    // foreach key, create newdata starting from initDate (and not from any date before)
    foreach ($keys as $key){
        $newData[$key]=[];
        if (isset($data[$key])){
            foreach ($data[$key] as $date=>$val){
                if ($date>$initDate){
                    $newData[$key][$date]=$val;
                }
            }
        }
        
    }
    
    //var_dump($newData);
    return $newData;
    
}

function updateStockPlu(){
    $query="SELECT ean FROM prod_articles LIMIT 3";
    $allArticleEan=query_table($query);
    array_shift($allArticleEan);
    $order['myRange']=52;
    displayinhtml($allArticleEan);
    //var_dump($allArticleEan);
    echo "<br><br>";
    foreach($allArticleEan as $row){
        //var_dump($row);
        $order['ean']=$row['ean'];
        $ean=$row['ean'];
        echo "<br>ean<br>".$row['ean']."<br>";
        $data=getStockValue($order);
        $stockArray=$data['stock'];
        $init=1; // no load if 0
        foreach ($stockArray as $date=>$val){
            $input=[];
            $input['thedate']=$date;
            $input['ean']=$ean;
            $input['stock']=$val;
            if ($val>0){$init=0;}
            if (!$init){
                $query="SELECT * FROM prod_stock WHERE thedate='$date' and ean='$ean'";
                //echo $query."<br>";
                $table=query_table($query);
                if (sizeof($table)>2){
                    echo "Problem: two same date and ean<br>";
                    echo "$ean and $date";
                }
                else{
                    $tableName="prod_stock";
                    if (sizeof($table)==1){// no value available
                        $filter=array_keys($input);
                        $query=create_INSERT($tableName,$input,$filter); 
                        
                    }
                    else{// update value
                        if($input['stock']!=$table[1]['stock']){
                            $input['id']=$table[1]['id'];
                            $filter=array_keys($input);
                            $primaryName='id';
                            $query=create_UPDATE($tableName,$input,$filter,$primaryName);
                            echo "change ".$table[1]['stock']." into ".$input['stock']."<br>";
                        }
                    }
                    echo $query."<br>";
                }
            }
            
        }
        
    }
}



function graphStockNew($data,$designation){

    // need to number graph if not unique
    if (!isset($_SESSION['graphNb'])){$_SESSION['graphNb']=0;}
    $graphId=$_SESSION['graphNb']+1;
    $_SESSION['graphNb']=$graphId;
    
    
    // prepare values for javascript script
    $str=[];
    $keys=['date','stock','sales','inventaire','livraison','diffStock','inventairePop'];
    foreach ($keys as $val){
        $str[$val]="[";
    }
    $str['background']="[";
    $ycomma="";
    foreach ($data['stock'] as $k=>$row){
        foreach ($keys as $val){
            if ($val=="date"){
                $str['date'].=$ycomma."'".convert_date($k,0,3,1)."'";
            }
            else{
                //echo $val." ".$k;
                if (isset($data[$val][$k])){
                    $str[$val].=$ycomma."'".number_format(floatval($data[$val][$k]),2)."'";
                }
                else{
                    $str[$val].=$ycomma."NaN";
                }
            }
        }
        $ycomma=",";
        $str['background'].=$ycomma."'blue'";
    }
    foreach ($keys as $val){
        $str[$val].="]";
        //echo $str[$val]."<br>";
    }
    $str['background'].="]";
    $title=addslashes("Stock de $designation");

    $outstr="<div class='graphContainer' ><canvas id='$graphId' ></canvas></div>";
    //echo $str['diffStock'];
        
        
    $outstr.="<script>
        var chartData={
            labels: ".$str['date'].",
            datasets:[{
                type: 'bar',
                label: 'ventes',
                backgroundColor: ".$str['background'].",
                data:".$str['sales']."
            },
            {
                type: 'bar',
                label: 'livraisons',
                backgroundColor: 'green',
                data:".$str['livraison']."
            },
            {
                type: 'bar',
                label: 'adjust',
                backgroundColor: 'red',
                data:".$str['diffStock']."
            },
            {
                type: 'line',
                label: 'inventaire',
                backgroundColor: 'red',
                borderColor:'red',
                showLine:0,
                pointRadius:8,
                fill:false,
                data:".$str['inventaire']."
            },
            {
                type: 'line',
                label: 'inventairePop',
                backgroundColor: 'orange',
                borderColor:'red',
                showLine:0,
                pointRadius:4,
                fill:false,
                data:".$str['inventairePop']."
            },
            {
                type: 'line',
                label: 'stock',
                backgroundColor: 'black',
                borderColor:'black',
                fill:false,
                data:".$str['stock'].",
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
            
    return $outstr;
   
}
  
  

?>
