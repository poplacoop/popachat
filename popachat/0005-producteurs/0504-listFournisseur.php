<?php
function createListeFournisseur($query,$primary,$filter){
    //-----------------------------------------------------------------------
    // $query to be given 
    // $filter are elements to give
    // $special are special...
    $class='theCommand';
    // get data
    //echo $query;
    $tbl=query_table_dico($query);
    //dispArray($tbl[0]);
    //dispArray($special);
    if ($tbl!=[]){  
        //--------------put tri or not  
        $tri=0;
        
        // prepare headers for table
        $headstr= "\n<table class='$class'>\n";
        $headstr.= "
        <tr>";
        // add tri or not...
        $label=['id'=>'No','titre'=>'Nom','referentPop'=>'referent Pop','referentPop2'=>'référent Pop'];

        foreach($filter as $val){
                $headstr.="<th>".$label[$val]."</th>";
        }
        
        
        $headstr.= "</tr>\n";

        //------------------------------------------------------------------
        // start loop
        $str="";
        $totalOneByOne=0;
        //dispArray($tbl[1]);
        foreach($tbl as $row){
            
                
            
            
            //var_dump($filter);
            
            foreach($filter as $key){
                if ($key=="titre"){
                    $str.="\n<td class='".$key."' ><button type='button' name='id' value='".$row['id']."'>".$row[$key]."</button>";
                    //if(!isset($_SESSION['fournisseur'])){$str.= "<div class='hide'>Le fournisseur est no ".$row['fournisseur']."</div></td>";}
                }
                else{
                    $str.="\n<td class='".$key."' >".$row[$key]."</td>";
                }
            }
            /*if (in_array('prixVente',$filter)){
                $prixVente=mynumber_format($row['prixAchat']*(($row['tva']==1)?1.055:1.2)/(1-$marque),2);
                $str.="<td>$prixVente</td>";
            }*/
            

            
           
            
            
        
        
          
            
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
