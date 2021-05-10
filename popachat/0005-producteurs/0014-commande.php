<?php
@session_start();
if (isset($_REQUEST['commande'])){
    header("Location:0014-commande.php");
}
//var_dump($_SESSION);
$_SESSION['listeDesProduits']="commande";
include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0503-stockFunctions.php";

//include "../0021-functions/0409-generateDico.php";
//include "0505-aliasCommandListClean.php";


//var_dump($refFourAliasDico);
echo myheader();
//-----------------------------------------------------------------------
// treat command

$commandeId=$order['commande'];
if ($commandeId!=""){
    //var_dump($_REQUEST);
    //echo "<br><br>";
    //if(!isset($_SESSION['graphEan'])){$_SESSION['graphNb']=1;}  // activate the graph
    //
    if(isset($_REQUEST['eraseZero'])){
        $query="SELECT id from prod_commandeList where commande_id='".$order["commande"]."' and (quantite=0 or isnull(quantite))";
        $deleteQuery="DELETE from prod_commandeList where id in ($query)";
        
        simple_query($deleteQuery);
    }
    // export AEM
    if ($_SESSION['userInfo']['admin']){
        if (isset($_REQUEST['exportAem'])){
            $query="UPDATE prod_commande SET exportAem='".$_REQUEST['exportAem']."' where id='".$commandeId."';";
            //echo $order[$val]." ".$val." ".$query;
            simple_query($query); 
        }
    }

    //----------------------------------------------------------------------
    // update information on the command
    //----------------------------------------------------------------------
    $commandeId=$order['commande'];
    // get Data from commande
    $commandeQuery="SELECT * FROM prod_commande WHERE id='".$order["commande"]."' order by date_livraison_prevue";
    $commandeTable=query_table($commandeQuery);
    //var_dump($commandeTable);
    //displayTableInHtml($commandeTable);
    $cmdInfo=$commandeTable[1];
    $order['fournisseur']=$cmdInfo['fournisseur'];
    $progress=$cmdInfo['progress']; // variable for progress
    $_SESSION['fournisseur']=$order['fournisseur'];
    //var_dump($cmdInfo);
    /*if ($order['proteger']!=""){
      
        $query="UPDATE prod_commande SET lock_commande='".$order['proteger']."' WHERE id='".$commandeId."';";
        //echo $query;
        simple_query($query);   
        
    }*/
    $list=['date_livraison_effective','date_envoi','date_traitement_facture','lock_commande','lock_livraison','lock_facture'];
    $progressList=['lock_commande'=>1,'lock_livraison'=>2,'lock_facture'=>3];
    foreach ($list as $val){
        if ($order[$val]!=""){
            if (substr($val,0,4)=='lock'){
                if ($order[$val]!="0"){
                    $query="UPDATE prod_commande SET progress='".$progressList[$val]."' where id='".$commandeId."';";
                    //echo $order[$val]." ".$val." ".$query;
                    $progress=$progressList[$val];
                    simple_query($query);  
                }
                else{
                    $query="UPDATE prod_commande SET progress='".($progressList[$val]-1)."' where id='".$commandeId."';";
                    //echo $order[$val]." ".$val." ".$query;
                    $progress=$progressList[$val]-1;
                    simple_query($query); 
                }
            }      
            $query="UPDATE prod_commande SET ".$val."='".$order[$val]."' where id='".$commandeId."';";
            //echo $query;
            simple_query($query);   
            if ($val=='date_livraison_effective'){
                $query="UPDATE prod_commande SET date_livraison_prevue='".$order[$val]."' where id='".$commandeId."';";
                simple_query($query);
                //echo $query;
            }
        }
    }

    //----------------------------------------------------------------------
    // Retrieve DATA
    // clean to cancel alias in commandes
    include "0505-aliasCommandListClean.php";

    $produitrefFourDico=create_product_dictionnary($listeProduitsTable,"refFour");

    //print_r($produitrefFourDico);
    //var_dump($_REQUEST);
    //----------------------------------------------------------------------
    // treat importation
    //var_dump($_REQUEST);
    
    $importResults="";
    if ($order['upload']){
        // 
        //var_dump($_REQUEST['upload']);
        $str="";  // create empty string if import gives nothing.
        $rowNb=floor($_REQUEST['upload']);
        echo "<br><br>";
        //print_r($_SESSION['importFile'][$rowNb]);
        $importFileRow=$_SESSION['importFile'][$rowNb];
        //var_dump($importFileRow);
        $try=$order['try'];
        //echo "try".$try;
        //echo "rownb".$rowNb;
        $importFileName=$_SESSION['importFile'][$rowNb]['filename'];
        $module=$_SESSION['importFile'][$rowNb]['module'];
        $format=$_SESSION['importFile'][$rowNb]['format'];
        echo $importFileName." avec le module $module et le format ".$format;
        //var_dump($_REQUEST['upload']);
        include $importFileName;
        $importResults=$str;
    }
    //if ($order['upload_csv']!=""){
    //    
    //    include "511-upload_csv.php"; // 
    //}
    
    //if ($order['upload_xls']!=""){
        //include "512-upload_excel.php";
        //$importResults=$str;
    //}//
    //echo "finish";
    include "0505-aliasCommandListClean.php"; // clean after update.
    //----------------------------------------------------------------------
    // Create Commande
    
    $failedCreateCommande="";

    //-----------------------------------------------------------------------
    // Modifier quantité
    if (isset($_REQUEST['editQuantite'])){
        $editQuantite=$_REQUEST['editQuantite'];
        $theId=$_REQUEST['editId'];
        //var_dump($theId);
        for ($i=0;$i<sizeof($editQuantite);$i++){
            $query="update prod_commandeList set quantite='".floor($editQuantite[$i])."' where id='".$theId[$i]."';";
            //echo $query."<br>";
            simple_query($query);
        }
    }

    //----------------------------------------------------------------------
    // Retrieve DATA
    if ($order['fournisseur']!=""){
        $where=" WHERE fournisseur=".$order['fournisseur'];
        $francoQuery="SELECT *,francoMontant as franco from prod_fournisseur where id=".$_SESSION['fournisseur'];
        $francoTable=query_table($francoQuery);
        
        }
    // take all where validated=1
    $listeProduitsQuery="SELECT * FROM (SELECT * FROM prod_articles WHERE validated<2) as prod_articles".$where;
    //echo "where$where";
    //echo $listeProduitsQuery;
    $listeProduitsTable=query_table($listeProduitsQuery);
    $produitDesignationDico=create_one_field_dictionnary($listeProduitsTable,"ean","designation");

    //----------------------------------------------------------------------
    //----------------------------------------------------------------------
    // Start creating html

    //displayTableInHtml($commandeListTable);
    // Treat Fournisseur
    // Get Data Fournisseur
    $listeFournisseurQuery="SELECT * FROM prod_fournisseur ORDER BY titre ASC";
    $listeFournisseurTable=query_table($listeFournisseurQuery);
    //fournisseurkDico=create_one_field_dictionnary($listeFournisseurTable,'id','titre');
    //----------------------------------------------------------------------
    // Get Data for Selected Commande
    //
    $cmdInfo="";
    //if ($commandeId!=""){
        
    $commandeQuery="SELECT * FROM prod_commande WHERE id='$commandeId' ";
    $commandeTable=query_table($commandeQuery);
    //var_dump($commandeTable);
    //displayTableInHtml($commandeTable);

    $cmdInfo=$commandeTable[1];
    $order['fournisseur']=$cmdInfo['fournisseur'];
    //var_dump($cmdInfo);

    //}
    //echo "lock_commande$lock_commande";

    // Get Data Commande List
    //$commandeQuery="SELECT * FROM prod_commande WHERE fournisseur='".$order['fournisseur']."' and active=1 ORDER BY date_livraison_prevue";
    //$commandeListTable=query_table($commandeQuery);
    
    //----------------------------------------------------------------------
    // get Import data
    //
    
    $query="SELECT * from prod_import_fournisseurs WHERE fournisseur='".$order['fournisseur']."'";
    $table=query_table($query);
    $htmlImport="";
    if (sizeof($table)>1){
        //displayinhtml($table);
        $htmlImport="<div id='import' ><div>"; 
        $htmlImport.= "<input type='file' name='fileToUpload' id='fileToUpload'>";
        $htmlImport.=$cmdInfo['file'];
        
        $_SESSION['importFile']=$table;
        $htmlImport.="<table>";
        for($r=1;$r<sizeof($table);$r++){
            $row=$table[$r];        
            $htmlImport.="<tr><td><input type='radio' id='upload' name='upload' value=$r>".$row['buttonMsg']."</input></td></tr>";
        }
        $htmlImport.="</table>";
        $htmlImport.="Essai?<input type='checkbox' id='try' name='try' value=1 checked></input>";
        $htmlImport.="<button id='go' name='go' value=0 >importer</button>";
        $htmlImport.="</div></div>";
    }
    else{
        $htmlImport.="<div id='import'>Pas de module d'import disponible</div>";
    }
    //$htmlImport.="</table>";
    //----------------------------------------------------------------------
    //if ($commandeId!=""){
    // Download the command from the database
    if ($_SESSION['tridisp']==1){$queryTri=" order by ARTI.tri";}else{$queryTri=" order by ARTI.designation";}
    $commandQuery="SELECT LIST.*,ARTI.designation,ARTI.tri,ARTI.refFour,ARTI.departement, ARTI.famille,ARTI.groupe, ARTI.fournisseur, 
    ARTI.tva,ARTI.conditionnement,ARTI.contenance, ARTI.uniteContenance, ARTI.uniteVente,ARTI.validated FROM
               (SELECT * FROM prod_commandeList WHERE commande_id=$commandeId order by ean) AS LIST         
               LEFT OUTER JOIN 
               (SELECT * FROM (SELECT * FROM prod_articles where validated<2 order by ean) as prod_articles ) AS ARTI
               ON LIST.ean=ARTI.ean $queryTri";
    
    $commandeListTable=query_table($commandQuery);
    
    //-----------------------------------------------------------------------
        // Modifier Prix with discount
        //array_shift($commandeListTable);
        //dispArray($commandeListTable[0]);
        //echo "discount=".$francoTable[1]['discount'];
        if (isset($_REQUEST['discount'])){
            $coef=1-$francoTable[1]['discount'];
            $remiseOn=1;
            if ($cmdInfo['appliedDiscount']==1){
                $coef=1/$coef;
                $remiseOn=0;
                
            }
            
            for ($i=1;$i<sizeof($commandeListTable);$i++){
                $query="update prod_commandeList set 
                prixAchat='".$commandeListTable[$i]['prixAchat']*$coef."' 
                where id='".$commandeListTable[$i]['id']."';";
                //echo "<br>".$query;
                simple_query($query);
            }
            $query="UPDATE prod_commande SET appliedDiscount=$remiseOn WHERE id=$commandeId";
            //echo "<br>".$query;
            simple_query($query);                
            $cmdInfo['appliedDiscount']=$remiseOn;
        }  
        //--------------------------------------------------------------
        
    
    
    include "0507-AdjustPricesCommandeOnWeek.php";
    //if ($_SESSION['userInfo']['admin']){
        // generate excel output if defined.
       // include "0512-export_excel_commande.php";
    //}
    //----------------------------------------------------------------------
    // EXPORT
    //echo "coucou";
    echo exportPrixStockXLS($commandeListTable);   
    //----------------------------------------------------------------------
    // Display commande en cours.
    //----------------------------------------------------------------------
    // List
    //var_dump($cmdInfo);
    $noExport=1-floor($order['export']);
    //echo "size=".sizeof($commandeListTable)."X";
    $editable=1-$cmdInfo['lock_commande'];
    //$filter=['ean','refFour','designation','conditionnement','unite','quantite','prixAchat',];
    //include "0500-listFunctions.php";
    // echo $order['historique'];
    
    //$graph=$order['graphEan']; // Prepare graphics
    //$stokShow=$graph; // shows stock
    $filter=['ean','refFour','designation','conditionnement','uniteVente'];
    $special=['quantite','prixAchat','cumul','erase','euro','checkQuantite','details'];
    $total="";

    //$commandeQuery="SELECT id,ean,quantite from prod_commandeList WHERE commande_id=".$order['commande'];
    $htmlChosenCommande="<div id='listeCommande'>";
    $htmlChosenCommande.=createListe($commandQuery,'id',$total,$filter,$special,$order);
    $htmlChosenCommande.="</div>";
    $htmlChosenCommandeNotEmpty=($htmlChosenCommande!="<div id='listeCommande'></div>");

    //----------------------------------------------------------------------
    // Header
    //----------------------------------------------------------------------
        $img=["../0101-images/hourglass-512.png","../0101-images/hourglass-512.png","../0101-images/hourglass-512.png"];
        $class=["locked","unlocked","unlocked","unlocked"];
        //echo $commandeId."=".$cmdInfo['lock_commande'];
        if (date_create($cmdInfo['date_envoi'])>date_create("2000-01-01")){ // check if date has been defined.
            $class0="locked";
            for ($i=0;$i<$progress;$i++){
                
                $img[$i]='/0101-images/locked.png';
                $class[$i+1]='locked';
                $img[$i+1]='../0101-images/open.png';
                $class[$i+2]='unlocked';
            }
            
        }
        
        $htmlWorkFlow="<div id='workflow'>
        <table>";
        $htmlWorkFlow.="<tr>
        <td id='nb' class='".$class[0]."'>
        no $commandeId
        </td>";
        $htmlWorkFlow.= "
        <td class=".$class[1].">
        <table><tr><td>envoyée le</td></tr><tr><td><input type='date' name='date_envoi' value='".$cmdInfo['date_envoi']."'  /></td></tr></table>
        </td>
        <td class='".$class[1]."' ><button class='".$class[1]."' name='lock_commande' value='".(1-$cmdInfo['lock_commande'])."'><img class=".$class[1]."  src=".$img[0]." /></button></td>";
        if ($cmdInfo['exportAem']){$classExport="locked";$exportAem=0;}else{$classExport="unlocked";$exportAem=1;}
        $htmlWorkFlow.="<td class=$classExport><button name='exportAem' value=$exportAem>AEM</button></td>";

        $htmlWorkFlow.= "<td class=".$class[2].">
        <table><tr><td>livrée le</td></tr><tr><td><input type=date name='date_livraison_effective' value='".$cmdInfo['date_livraison_effective']."' onchange='submit()'></td></tr></table>
        </td>
        <td class=".$class[2]." ><button  class='".$class[2]."' name='lock_livraison' value='".(1-$cmdInfo['lock_livraison'])."'><img  class=".$class[2]." src=".$img[1]." /></button></td>";
        $htmlWorkFlow.= "<td class=".$class[3].">
        <table><tr><td>facture reçue le</td></tr><tr><td><input type=date name='date_traitement_facture' value='".$cmdInfo['date_traitement_facture']."' onchange='submit()'></td></tr></table>
        </td>
        <td class=".$class[3]." ><button  class=".$class[3]." name='lock_facture' value='".(1-$cmdInfo['lock_facture'])."'><img  class=".$class[3]." src=".$img[2]." /></button></td>
        <td id='total' class=".$class[3]." >".mynumber_format($total,2)."</td>
        </tr></table>";
        

        if ($htmlChosenCommandeNotEmpty){
            //----------------------------------------------------------
            // gestion du franco
            //
            $query="SELECT quantite/conditionnement as colis, A.prixAchat*Quantite as amount from prod_commandeList as A 
            left outer join prod_articles as B 
            on A.ean=B.ean where A.commande_id=".$commandeId;
              
            $totalsQuery="SELECT sum(amount) as total,sum(colis) as colisNb from ($query) as A";
            //echo $totalsQuery;
            $totalsTable=query_table($totalsQuery);
            $totalColis=$totalsTable[1]['colisNb'];
            
            $francoMontant=$francoTable[1]['francoMontant'];
            $francoColis=$francoTable[1]['francoColis'];
            
            if ($total>=$francoMontant){$htmlWorkFlow.="<div class='franco'>Le total en valeur est $total, le franco $francoMontant est atteint</div>";}
            else{$htmlWorkFlow.="<div class='nofranco'>Le total en valeur est $total, le franco $francoMontant n'est pas atteint</div>";}
            
            if ($totalColis>=$francoColis){$htmlWorkFlow.="<div class='franco'>Le nombre de colis est $totalColis, le franco  $francoColis est atteint</div>";}
            else{$htmlWorkFlow.="<div class='nofranco'>Le nombre de colis est $totalColis, le franco  $francoColis n'est pas atteint</div>";}
            //
            //----------------------------------------------------------
            
            
        }
        $htmlWorkFlow.="</div>";
        
        //$htmlChoix= "<div id='choix' >";
        //$htmlChoix.=$htmlChosenCommande;
        //$htmlChoix.= "</div>";
        
    //----------------------------------------------------------------------
    // Create bon de livraison
    //----------------------------------------------------------------------
      
        //include "0600-creationBonDeLivraison.php";
    //}
    //else{
    //    $str="Choisir une commande dans le menu commande";
    //}

    //----------------------------------------------------------------------
    //----------------------------------------------------------------------

    
    //----------------------------------------------------------------------
    // Create the body of the page
    //----------------------------------------------------------------------
    //----------------------------------------------------------------------

    // start html
    //----------------------------------------------------------------------
    // prepare menu
        echo "<body>
        <div class='topBanner'>";
    echo menu($menuFilter);

    // Prepare display
    echo "<h1>Commande</h1>";
    echo $importResults;
    // display commande and fournisseur
    echo "<div class='chosen'>".$order['fournisseur']."-".$dico['fournisseur'][$order['fournisseur']]."</div>";
    echo "<input type='hidden' name='fournisseur', id='fournisseur' value='".$order['fournisseur']."'></input>";
    echo "<div class='chosen '>".$cmdInfo['date_livraison_prevue']." </div>";
    echo "<input type='hidden' name='commande', id='commande' value='$commandeId'></input>";
    //$selectedId="";




    echo $failedCreateCommande;

    echo "<form id='myForm' method='post' enctype='multipart/form-data'>";
    //echo "<input type='hidden' name='liked' value=".$_SESSION['liked']."</input>";
    $menuImportation=strpos($_SESSION['userInfo']['userRights'],"P");
    
    echo "<div class='titre'>Choisissez les quantités et ajustez les prix<br>
    Pour supprimer ou ajouter des produits, utilisez le menu 'liste'.
    </div>";
    //$menuImportation=1;
    if ($menuImportation){    
        echo $htmlImport;
    }
    /*if ($commandeId==""){
        // Liste des commandes
        echo list_of_commandes($order);
    }*/
    //echo $insertMsg;

    echo "<div class='topBanner'>";
        
        echo "<div class='topLeft'>";
            //echo $htmlChoixHeader;
            //echo $htmlChoixListe;
            //echo $htmlChoix;
            echo $htmlChosenCommande;
            //echo $htmlAddLine;
        echo "</div>";
        //echo $htmlFamille;
    echo "</div>";
    if ($htmlChosenCommandeNotEmpty){
        
        echo "<div>";
        if ($cmdInfo['appliedDiscount']==0){
            echo "<button name='discount'>APPLIQUER REMISE: -
                ".number_format($francoTable[1]['discount']*100,1)."</button>";
        }
        else{
            echo "<button name='discount'>ENLEVER REMISE: -
                ".number_format($francoTable[1]['discount']*100,1)."</button>";
        
        }
        echo "<button >ENREGISTRER</button>
        <span id='message'></span>
        <button name='eraseZero' >EFFACER LES QUANTITES NULLES?</button>
        </div>";
    }
       
    
    echo $htmlWorkFlow;
    //echo "prg=".$progress;
    //var_dump($img);
    //echo "lock commande".$order['lock_commande'];
    if (($htmlChosenCommandeNotEmpty)&& ($cmdInfo['lock_commande'])){
        echo "<div id='enregistreBonStock'>
                <div class='troisMenus'>
                    <!--<div><a href='./files/bonDeCommande.pdf'>Bon</a></div>-->
                    <div><a href='./files/priceSeuls.xls'>Prix Seuls</a></div>
                    <div><a href='./files/price.xls'>Articles et Prix</a></div>
                    <div><a href='./files/colisLivraison.xls'>Colis</a></div>
                </div>
            </div>";
    }
    else{
        echo "<div class='realErase'>Commande non validé ou vide</div>";
    }
     

    if ($order['select']!=""){
        echo "ean choisi";
        $ean=$order['select'];
        
    }

    //echo $htmlChosenCommande;

    //echo $htmlList;
    //echo "<input type='hidden' id='graphEan' name='graphEan' value='".$order['graphEan']."'></input>";
    //echo "<input type='hidden' id='fournisseur' name='fournisseur' value='".$order['fournisseur']."'></input>";
    echo "</form>";
}
else{
    echo "commande non selectionnée";
    header("Location:0012-commandes.php");
}

?>
<script>
$(document).ready(function() {
    function activateButtons(){
        $('.imgQuantite').click(function() {
            pos=4
            if( $('.theCommand').length )  {       // use this if you are using id to check{
                $('.theCommand').on('click','.imgQuantite',function() {
                    console.log("imgQuantite is clicked");                   
                    $(".theCommand").find('.quantite').each(function(index,value){
                        //function(){
                        console.log($(this).attr("class"));
                        console.log($(this).find('input').length);
                        // if the td elements contain any input tag
                        if ($(this).find('input').length>0){
                            console.log("input exists");
                            val=$(this).find('input').val();
                            console.log ("replace"+val);
                            console.log($(this).html());
                            $(this).html(val);
                            // sets the text content of the tag equal to the value of the input
                            
                        }
                        else {
                            console.log("input n'existe pas");
                            // removes the text, appends an input and sets the value to the text-value
                            var cellVal = $(this).text();
                            //id=$(this).attr("myid");
                            lineId=$(this).parents().children().eq(index).find('.imgEdit').attr("myid");
                            //console.log("jkj"+$(this).parents().children().eq(index).find('.imgEdit').attr("myid"));
                            msg="<input name='quantite[]' value="+cellVal+"></>";
                            //console.log(msg);
                            //alert(theid);
                            $(this).html(msg);
                            /*$(this).keypress(function (e) {
                                  if (e.which == 13) {
                                    console.log("enter"+index);
                                    console.log($(this).parents().parents().children().eq(index+2).children().eq(pos).html());
                                    $(this).parents().parents().children().eq(index+2).children().eq(pos).get(0).focus();
                                    //$('form#login').submit();
                                    
                                    $(this).parents().parents().children().eq(index+2).children().eq(pos).focus();
                                    return false;    //<---- Add this line
                                  }
                                });*/
                        }
                    });
                });
            }
        });
        $('.theCommand').on('keydown','input[name="quantite[]"]',function(e){
            console.log(e.keyCode);
            if(e.keyCode==13){
                event.preventDefault();
                if ($('#commande').val()!=""){
                console.log("quantite nouvelle ");
                console.log($(this).val());
                console.log($(this).parents("tr").find(".ean").html());
                ean=$(this).parents("tr").find(".ean").html();
                
                cond=$(this).parents("tr").find(".conditionnement").html();
                colis=Math.floor($(this).val()/cond+0.5);
                quantite=colis*cond;
                if ($(this).val()!=quantite){alert("Attention: la quantité a été modifié car incompatible avec le conditionnement");}
                console.log("conditionnement"+$(this).parents("tr").find(".conditionnement").html());
                //query="update prod_commandeList set quantite="+quantite+" where ean="+$(this).next().val();
                $(this).val(quantite);
                $(this).parents("tr").find(".colis").html(colis);
                query="update prod_commandeList set quantite="+quantite+" where ean="+ean+" and commande_id="+$("#commande").val();
                console.log("update quantite="+query);
                $.ajax({
                    url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: {query: query},
                    success: function(response){ 
                        //$(this).html(response);
                        console.log(response);
                    } 
                });
                //$('#myForm').submit();
                
                var inputs = $(this).parents("form").eq(0).find("input[name='quantite[]']");
                console.log("objet is "+$(this).parents("form").html());
                if (inputs[inputs.index(this) + 1] != null) {  
                    console.log("index is "+ (inputs.index(this) ));                 
                    inputs[inputs.index(this) + 1].focus();
                }
                else{
                    inputs[0].focus();
                }
                //inputs['37'].focus();
                }
                
            }
        });
        
        //------------------------------------------------------------------------------------------
        // change colis in database
        $('.theCommand').on('keydown','input[name="colis[]"]',function(e){
            console.log(e.keyCode);
            if(e.keyCode==13){
                event.preventDefault();
                if ($('#commande').val()!=""){
                console.log("quantite nouvelle ");
                console.log($(this).val());
                console.log($(this).parents("tr").find(".ean").html());
                ean=$(this).parents("tr").find(".ean").html();
                
                cond=$(this).parents("tr").find(".conditionnement").html();
                colis=$(this).val();
                quantite=colis*cond;
                console.log("conditionnement"+$(this).parents("tr").find(".conditionnement").html());
                //query="update prod_commandeList set quantite="+quantite+" where ean="+$(this).next().val();
                $(this).val(colis);
                $(this).parents("tr").find(".quantite").html(quantite);
                query="update prod_commandeList set quantite="+quantite+" where ean="+ean+" and commande_id="+$("#commande").val();
                console.log("update quantite="+query);
                $.ajax({
                    url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: {query: query},
                    success: function(response){ 
                        //$(this).html(response);
                        console.log(response);
                    } 
                });
                //$('#myForm').submit();
                
                var inputs = $(this).parents("form").eq(0).find("input[name='colis[]']");
                console.log("objet is "+$(this).parents("form").html());
                if (inputs[inputs.index(this) + 1] != null) {  
                    console.log("index is "+ (inputs.index(this) ));                 
                    inputs[inputs.index(this) + 1].focus();
                }
                else{
                    inputs[0].focus();
                }
                //inputs['37'].focus();
                }
                
            }
        });
        
        
        
        
        
        $('.theCommand').on('keydown','input[name="prixAchat[]"]',function(e){
            console.log(e.keyCode);
            if(e.keyCode==13){
                event.preventDefault();
                if ($('#commande').val()!=""){
                console.log("nouveau prix ");
                console.log($(this).val());
                console.log($(this).parents("tr").find(".ean").html());
                ean=$(this).parents("tr").find(".ean").html();
                query="update prod_commandeList set prixAchat="+$(this).val()+" where ean="+ean+" and commande_id="+$("#commande").val();
                console.log("update prixAchat="+query);
                $.ajax({
                    url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: {query: query},
                    success: function(response){ 
                        //$(this).html(response);
                        console.log(response);
                    } 
                });
                //$('#myForm').submit();
                
                var inputs = $(this).parents("form").eq(0).find("input[name='prixAchat[]']");
                console.log("length is "+inputs.length);
                if (inputs[inputs.index(this) + 1] != null) {  
                    console.log("index is "+ (inputs.index(this) ));                 
                    inputs[inputs.index(this) + 1].focus();
                }
                else{
                    inputs[0].focus();
                }
                //inputs['37'].focus();
                }
                
            }
        });
        //
        //
        //
        $('.theCommand').on('blur','input[name="prixAchat[]"]',function(){
            //console.log(e.keyCode);
            //if(e.keyCode==13){
                event.preventDefault();
                if ($('#commande').val()!=""){
                    console.log("nouveau prix ");
                    console.log($(this).val());
                    console.log($(this).parents("tr").find(".ean").html());
                    ean=$(this).parents("tr").find(".ean").html();
                    query="update prod_commandeList set prixAchat="+$(this).val()+" where ean="+ean+" and commande_id="+$("#commande").val();
                    console.log("update prixAchat="+query);
                    $.ajax({
                        url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                        type:'POST',
                        data: {query: query},
                        success: function(response){ 
                            //$(this).html(response);
                            console.log(response);
                        } 
                    });
                }
                
            //}
        });
  
        
        
        $('.imgPrice').click(function() {
            pos=5
            if( $('.theCommand').length )  {       // use this if you are using id to check{
                $('.theCommand').on('click','.imgPrice',function() {
                    console.log("imgPrice is clicked");                   
                    $(".theCommand").find('.prixAchat').each(function(index,value){
                        //function(){
                        console.log($(this).find('input').length);
                        // if the td elements contain any input tag
                        if ($(this).find('input').length>0){
                            console.log("input exists");
                            // sets the text content of the tag equal to the value of the input
                            
                        }
                        else {
                            // removes the text, appends an input and sets the value to the text-value
                            var cellVal = $(this).text();
                            //id=$(this).attr("myid");
                            lineId=$(this).parents().children().eq(index).find('.imgPrice').attr("myid");
                            //console.log("jkj"+$(this).parents().children().eq(index).find('.imgEdit').attr("myid"));
                            msg="<input name='prixAchat[]' value="+cellVal+"></>";
                            //console.log(msg);
                            //alert(theid);
                            $(this).html(msg);
                        }
                    });
                });
            }
        });
        
            //--------------------------------------------------------------
        // Colis
        // change quantite into input
        $('#listeCommande').on('click','.imgColis',function(){
            console.log("colis");
            
            $('#listeCommande .chosen').each(function( index ) {
            console.log( index);
            //console.log($(this).children("td").children(".imgQuantite").attr("myid"));
            id=$(this).children("td").children(".imgColis").attr("myid");
            console.log(id);
            cell=$(this).children(".colis"); // quantite
            //console.log($(this).attr("class"));
            
            if (cell.find('input').length==0){
                val=cell.html();
                cell.html("<input  name='colis[]' value="+cell.html()+">");
                cell.append("<input  type='hidden' name='eanModif[]' value="+id+">");
                //cell.append("<input  name='quantite[]' value="+cell.html()+">");
            }
            else{
                console.log("effacer input");   
            }
            //console.log(cell.html());
            });
            
        });
        
        

        
        
    }
    //console.log($('#choix').children().first().children().first().html());
    
    $('.imgIcon').click(function() {
        id=$(this).attr("myid");
        if (confirm("Voulez vous vraiment effacer ce produit?")) {
            console.log("pere");
            console.log($(this).parents().first().parents().first().html());
            $(this).parents().first().parents().first().remove();
            erase(id);
        }
        
    });
    console.log("imgEdit and imgIcon activated");
    
    $('.imgStock').click(function() {
        id=$(this).attr("myid");
        console.log("stock");
        $('#graphEan').val(id);
        $('#myForm').submit();
        console.log("graph submit done");        
    });
    
    
    function erase(id){
        query='DELETE FROM prod_commandeList WHERE id='+id;
        
        //alert(query);
        //alert('Ne pas oublier de rafraichir la page');
            // modified: POST
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: { query: query},
                    success: function(response){ 
                                        
                        $(this).html(response);
                        alert(response);
                    } 
            });
    }
    


  
    console.log( "ready!" );
    $("#export").click(function() {
        //$("#export").val(1-$("#export").val());
        //alert($("#export").val());
        
    });
    
    
    $("#ean").keyup(function() {
        filteredList();
    }); 
    $("#design").keyup(function() {
        filteredList();
    });
    $("#refFour").keyup(function() {
        filteredList();
    });
    $("#four").keyup(function() {
        filteredList();
    });
    $("#fournisseur").change(function() {
        $('#choix').html("");
        $('#workflow').remove();
        // update of list of commands
        vfournisseur=$("#fournisseur").val();
        query="SELECT * FROM prod_commande WHERE fournisseur='"+vfournisseur+"' and active=1 order by date_livraison_prevue;";
        console.log(query);
        // modified: POST
        $.ajax({
                url : '406-listeCommande.php', // La ressource ciblée
                type:'POST',
                data: { query: query,select:<?php echo "'".$order['select']."'";?>},
                success: function(response){                    
                    $("#commandeList").html(response)},
                //fail: function (jqXHR, textStatus, errorThrown) { errorFunction(); }
        });
        
        // update of list of commands detailed
        vfournisseur=$("#fournisseur").val();
        query="SELECT * FROM prod_commande WHERE fournisseur='"+vfournisseur+"' and active=1 order by date_livraison_prevue;";
        console.log(query);
        // modified: POST
        $.ajax({
                url : '411-displayCommandList.php', // La ressource ciblée
                type:'POST',
                data: { query: query,fournisseur:vfournisseur},
                success: function(response){                    
                    $("#commandFullList").html(response)},
                //fail: function (jqXHR, textStatus, errorThrown) { errorFunction(); }
        });
        
        
        //console.log(response);
    });
    activateButtons();
    console.log("buttons activated");
    //$('.designation:button').click(function(event) { // <- goes here !
            //event.preventDefault(); // prevent from submitting
    //}   
    //});
    //filteredList();
    
    //Ouvre création de commande pour édition
    $('#listeCommande').on('click','button[name="ean"]',function(){
               
            ean=$(this).parents("tr").find(".ean").html();
            console.log(ean);
            window.open("./0015-creationDeProduits.php?ean="+ean);
            //$("#ean").val(ean);
            //formAdd.submit();


    });
    
    // display tri column
    $('#listeCommande').on('click','#tridisp',function(){
        console.log("tridisp clicked");
        console.log($('#tridisp').attr("class"));
        $('#tridisp').toggleClass("clicked");
        $('#tri').attr("class","icon tri");
        $('#myform').submit();
        //filteredList();
        
        
    });
    
});





$(".leftNav").find("div").eq(3).addClass('navSelected');
$('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#commande");
})
</script>
</body>
</html>


