<?php

function createListe($query,$primary,&$totalOneByOne,$filter,$special,$order,$call='js'){
    //-----------------------------------------------------------------------
    // $query to be given 
    // $filter are elements to give
    // $special are special...
    if (!isset($_SESSION['rupture'])){$_SESSION['rupture']=0;}
    //$param['edit': 0 or 1,
    //$primary="id";
    $class='theCommand';
    $id='theCommand';
    
    // get data
    //echo $query;
    $tbl=query_table_dico($query);
    //dispArray($tbl[0]);
    //dispArray($filter);
    
    
    if ($tbl!=[]){  
        //--------------put tri or not  
        $tri=$_SESSION['tridisp'];
        $pluFilter="";
        if (isset($_SESSION['fournisseur'])){
            if ($_SESSION['fournisseur']!=""){
                $pluFilter.=" AND fournisseur=".$_SESSION['fournisseur'];
            }
        }
        //echo $order['myRange'];
        
        $stockDico=getstockInformationDico($order['myRange'],"",$pluFilter);
        //var_dump($stockDico);
        //dispArray($stockDico['3770010033165']);
        //die;
        /*if (in_array('stock',$special)){
            // query for last value of stock
            $query="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
            FROM `prod_stock` where not isnull(stock) group by ean) as last 
            left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
            ORDER BY `last`.`thedate` ASC ";
            $stockDico=create_one_field_dictionnary_sql($query,'ean','stock');
        }
        //----------------------------------
        // Check quantites
        if (in_array('checkQuantite',$special)){
            
        }*/
        
        if (in_array('ventes',$special)){
            
            $query="Select ean,sum(quantite) as ventes from (SELECT * FROM `prod_plu` WHERE thedate > current_date- interval ".$order['myRange']." week ) as a group by ean";
            $ventesDico=create_one_field_dictionnary_sql($query,'ean','ventes');
        }
        
        if($order['commande']!=""){
                $commandeQuery="SELECT id,ean,quantite from prod_commandeList WHERE commande_id=".$order['commande'];
                $commandeTable=query_table($commandeQuery);
                $quantiteDico=create_one_field_dictionnary($commandeTable,"ean","quantite");
                $idDico=create_one_field_dictionnary($commandeTable,"ean","id");
                //var_dump($idDico);
                //echo "quant=".$quantiteDico['2000000002675'];
                if (in_array('editQuantite',$special)){
                    //echo "coucodqsdjfsmlk";
                    
                }
        }
        //----------------------------------
        // gestion des stock en rupture
        if($_SESSION['rupture']==1){
            // modify to take the last one.
                $ruptureQuery="SELECT ean,selected from prod_rupture_list where selected=1";
                $ruptureTable=query_table($ruptureQuery);
                $ruptureDico=create_one_field_dictionnary($ruptureTable,"ean","selected");
                //var_dump($idDico);
                //echo "quant=".$quantiteDico['2000000002675'];
        }
        
        //echo "stock is".$stockDico['2000000000138'];
        //var_dump($stockTable);
        
        // prepare headers for table
        $headstr= "\n<table class='$class' id='$id' >\n";
        $headstr.= "
        <tr>";
        // add tri or not...
        if ($tri){
            $label=['ean'=>'EAN','refFour'=>'fournisseur','tri'=>'tri','designation'=>'libellé','conditionnement'=>'cond','uniteVente'=>'unité'];
            array_push($filter,'tri');
        }
        else{
            $label=['ean'=>'EAN','refFour'=>'fournisseur','designation'=>'libellé','conditionnement'=>'cond','uniteVente'=>'unité'];
            
        }
        //----------------------------------
        foreach($filter as $key=>$val){
            if ($key==2){// put tri in 3rd column)
                //echo $val;
                $clicked=($_SESSION['tridisp']==1)?"clicked":"";
                $headstr.="<th>".$label[$val]."  <img class='icon' id='tri' src='../0101-images/uprightarrow.png' />
                <button   name='tridisp' class='tridisp $clicked' id='tridisp' value='".$_SESSION['tridisp']."'>tri</button>
                </th>";
            }
            else{
                $headstr.="<th>".$label[$val]."</th>";
            }
        }
        
        if (in_array('prixAchat',$special)){ $headstr.="<th id='prixhd'><button type='button' id='prixhd'>prix</button></th>";}
        if (in_array('quantite',$special)){ $headstr.="<th>Colis</th><th>Quantité</th>";}
        if (in_array('stock',$special)){        $headstr.="<th>Stock</th>";}
        if (in_array('ventes',$special)){        $headstr.="<th>Ventes</th>";}
        //if (in_array('ventes',$special)){        $headstr.="<th></th>";}
        if (in_array('cumul',$special)){
            $headstr.= "<th><button  name='nocumul' id='cumulhd' value='".($_SESSION['nocumul'])."'>cumul</button></th><th></th>";//echo "coucou";
        }
        if (in_array('checkQuantite',$special)){        $headstr.="<th>A com.</th>";}
        
        if (in_array('graphSales',$special)){        $headstr.="<th>A com.</th>";}
        if (in_array('classArticles',$special)){        $headstr.="<th></th><th id='selection'>Selection</th>";}
        $headstr.= "</tr>\n";

        //------------------------------------------------------------------
        // start loop
        $str="";
        $totalOneByOne=0;
        //dispArray($tbl[1]);
        foreach($tbl as $row){
            $class="";
            if($order['commande']!=""){
                if (array_key_exists($row['ean'],$quantiteDico)){
                    if ($call=='js'){
                        $class='chosen';
                    }
                    else{
                        $class='green';
                    }
                }
            }
            //----------------------------------------------------------------------------------------
            // prepare for warning
            // case defined
            $classCheck="lightyellow";
            if (isset($stockDico[$row['ean']]['commande1mois'])){
                // change class if too much
                
                //echo "quantite".$row['quantite'];

                $commande1mois=(floor($stockDico[$row['ean']]['commande1mois']/$row['conditionnement']-0.001)+1)*$row['conditionnement'];
                $commande2mois=(floor($stockDico[$row['ean']]['commande2mois']/$row['conditionnement']-0.001)+1)*$row['conditionnement'];
                
                
                if($stockDico[$row['ean']]['commande1mois']>0){// pas besoin de commander
                        $classCheck='pasassez';
                }   

                // if a command has been suggested
                if ((isset($quantiteDico[$row['ean']]))&&($class=="chosen")){ // case commande
                    $classCheck="ok";
                    //echo $row['ean']."=".$stockDico[$row['ean']]['commande2mois'];
                    if ($commande1mois>$quantiteDico[$row['ean']]){
                            $classCheck='pasassez';
                        }
                    else{
                        if ($commande1mois<$quantiteDico[$row['ean']]){
                            $classCheck='trop';
                        }
                    }
                }
            }
            
            //------------------------------------------------------------------
            // Start table line
            //-------------------------------------------------------------------
            
            
            $str.= "<tr class='$class'>";
            //dispArray($filter);
            // main loop with $filter keys
            foreach($filter as $key){
                
                if ($key=="designation"){
                    $class=$key;
                    if($_SESSION['rupture']==1){if(array_key_exists($row['ean'],$ruptureDico)){$class.=" rupture";}} // rupture
                    $nouvStr="";if($row['validated']==0){$nouvStr="<span class='nouveau'>Nouveau</span><br>";}
                    //echo $row['ean']."=".$row['validated'];
                    $str.="\n<td class='".$class."' ><button type='button' name='ean' value='".$row['ean']."'>$nouvStr".html_entity_decode($row[$key])."</button>";
                    if(!isset($_SESSION['fournisseur'])){$str.= "<div class='hide'>Le fournisseur est no ".$row['fournisseur']."</div></td>";}
                }
                else{
                        $str.="\n<td class='".$key."' >".$row[$key]."</td>";
                }
                
                
                
                
                
            }
            /*if (in_array('prixVente',$filter)){
                $prixVente=mynumber_format($row['prixAchat']*(($row['tva']==1)?1.055:1.2)/(1-$marque),2);
                $str.="<td>$prixVente</td>";
            }*/
            if (in_array('prixAchat',$special)){
                if (isset($row['prixAchat'])){
                    $prix=$row['prixAchat'];
                }
                else{
                    $prix=0;
                }
                
                $str.="<td class='prixAchat'>".mynumber_format($prix,2)."</td>";

            }

            if ((in_array('editQuantite',$special))||(in_array('quantite',$special))){
                //echo "editQuantite";
                if (isset($quantiteDico[$row['ean']])){
                    $quantite=$quantiteDico[$row['ean']];
                }
                else{
                    $quantite=0;
                }
                //echo "quantite=$quantite"."X"." X".$row['ean'];
                if ($row['conditionnement']==0){
                    echo "Attention le conditionnement est nul pour ".$row['ean'];
                    $str.="<td class='colis'></td>";
                }
                else{
                    $str.="<td class='colis'>".floor($quantite/$row['conditionnement'])."</td>";
                }
                $str.="<td class='quantite $classCheck'>".$quantite."</td>";

            }
            if (in_array('cumul',$special)){
                if (isset($row['quantite'])){
                    $quantite=$row['quantite'];
                }
                else{
                    $quantite=0;
                }
                $str.="<td >".mynumber_format($quantite*$row['prixAchat'],2)."</td>";
                $totalOneByOne+= $quantite*$row['prixAchat'];
                if($_SESSION['nocumul']==0){
                    $str.="<td class='cumul'>".mynumber_format($totalOneByOne,2)."</td>";
                }

            }
            if (in_array('stock',$special)){
                $stock=0;
                if(array_key_exists($row['ean'],$stockDico)){
                    if(array_key_exists('stock',$stockDico[$row['ean']])){
                        if ($stockDico[$row['ean']]['stock']!=""){
                            
                            $stock=$stockDico[$row['ean']]['stock'];
                            //$str.= "found$stock H";
                        }
                        else{
                            $stock="";
                        }
                    }
                }
                $str.= "<td class='stock' >".convert_number($stock)."</td>";
            }
            if (in_array('ventes',$special)){
                if (isset($ventesDico[$row['ean']])){
                    $ventes=$ventesDico[$row['ean']];
                }
                else{
                    $ventes="";   
                }
                $str.= "<td class='ventes' >".convert_number($ventes)."</td>";
            }
            //dispArray($special);
            //----------------------------------------------------------------
            //
            // Check quantites and color if above required
            //
            //var_dump($row);
            if (in_array('checkQuantite',$special)){
                $valueExist=false;
                if (isset($stockDico[$row['ean']]['commande1mois'])){

                    // fin commande
                    $str.="<td class='$classCheck' >".mynumber_format($commande1mois,1)."</td>";
                    //$str.="<td class='$class' >".$stockDico[$row['ean']]['commande2mois']."</td>";
                }
                else{
                    $str.="<td ></td>";
                }
            }
            
            //----------------------------------------------------------
            //
            // Display details

            if ((in_array('ventes',$special))||(in_array('details',$special))){
            $str.="<td>";
            $str.="<a href='0602-articlesDetails.php?ean=".$row['ean']."' target='_blank' ><img src='../0101-images/graph.png' class='imgGraph' ></img></a>";
                $str.="<input type='hidden' name='theId[]' value='".$row[$primary]."'>";  
            $str.="</td>"; 
            }
            
         
            
            //-----------------------------------
            // pencil to modify quantities
            if (in_array('quantite',$special)){
                if (isset($idDico[$row['ean']])){
                    $str.="<td class='box' ><img src='../0101-images/colis.png' class='imgColis' myid='".$row[$primary]."' ></img></td>";
                    $str.="<td class='crayon'><img src='../0101-images/pencil1600.png' class='imgQuantite' myid='".$row[$primary]."' ></img></td>";
                    
                }
            }
            
            if (in_array('euro',$special)){
                $str.="<td><img src='../0101-images/euroSign.png' class='imgPrice'></img></td>";
            }
            if (in_array('graphSales',$special)){
                $str.="</tr><tr><td colspan='10'>".plot_chart_sales($row['ean'],$row['ean']." ".html_entity_decode($row['designation']),$order)."</td>";
            }            
            if (in_array('classArticles',$special)){
                $str.="<td>
                <img class='minus' src='../0101-images/minus.png'>
                <span class='nb'>".$row['validated']."</span>
                <input type='hidden' name='validated[".$row['ean']."]' value='".$row['validated']."'>
                <img class='plus' src='../0101-images/plus.png'>
                </td>";
            }
            
            
            $str.= "</tr>\n";
        }
        $str.= "</table>\n";
        //$str="";
    }
    else{
        $str=""; $headstr="";
        $totalOneByOne=0;  
    }
    return $headstr.$str;
}


?>
