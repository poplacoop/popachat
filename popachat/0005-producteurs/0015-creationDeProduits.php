<?php
@session_start();
$_SESSION['listeDesProduits']='creationDeProduits';
include "0000-initFilesProd.php";
include "0003-prepareData.php";
include "../0021-functions/0409-generateDico.php";
//echo "commande".$_SESSION['commande'];
echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);


//dico['fournisseur'.'alf']
//var_dump($dico);
//var_dump($_SESSION['commande']);
//var_dump($_REQUEST);

//include "0410-getNewEan.php";

echo "<h1>Création de Produits</h1>";


$items=["ean"=>"ean","designation"=>"désignation","refFour"=>"reference fournisseur",
        "departement"=>"departement","famille"=>"famille",
        "fournisseur"=>"fournisseur",
        "tva"=>"tva","link"=>"lien internet","remarques"=>"Remarques",
        "prixAchat"=>"Prix d'Achat",
        "conditionnement"=>"Quantité par colis",
        "contenance"=>"Contenance","uniteContenance"=>"uniteContenance",
        "uniteVente"=>"uniteVente","groupe"=>"groupe"];

//----------------------------------------------------------------------
// IMPORT AEMSOFT
/*var_dump($_REQUEST);
if (isset($_FILES["importFiles"])){
        include "../0021-functions/510-imports_functions.php";
        include "../0010-admin/0106-import-articles-formatAEMsoft.php";
}*/

//----------------------------------------------------------------------
// modify alias
//----------------------------------------------------------------------

// First erase refFourAliases
$query="SELECT refFourAlias FROM prod_refFour_alias where refFour='".$order['refFour']."'";
$table=query_table($query,1);
$refFourOneDico=create_product_dictionnary($table,'refFourAlias');
//var_dump($refFourOneDico);
//var_dump($refFourOneDico['LEG185']);
//dispArray($_REQUEST['edit']);

//echo $refFourOneDico[0][1];
if (isset($_REQUEST['edit'])){    
    if ($_REQUEST['edit']!=""){
        $edit=$_REQUEST['edit'];
        foreach ($refFourOneDico as $key=>$val){
            //echo "key=".$key." = ";
            //dispArray($val);
            //echo "<br>";
            if (!in_array($key,$edit)){
                    $query="DELETE FROM prod_refFour_alias WHERE refFourAlias='".$key."'";
                    //echo $query."<br>";
                    simple_query($query);
            }
        }
    } 
}
// then create new refFourAliases
if (isset($_REQUEST['refFourAlias'])){
    if ($_REQUEST['refFourAlias']!=""){
        $query="INSERT into prod_refFour_alias (refFouralias,refFour) VALUES 
        ('".$_REQUEST['refFourAlias']."','".$_REQUEST['refFour']."')";
        //echo $query;
        simple_query($query);
        //header("Location:0601-articlesDetails.php?ean=$ean&myrange=".$order['myRange']);
    }
}


// retrieve aliases
$query="SELECT refFourAlias FROM prod_refFour_alias where refFour='".$order['refFour']."'";
$table=query_table($query,1);
$refFourOneDico=create_product_dictionnary($table,'refFourAlias');


//----------------------------------------------------------------------
// CREATE new Product
// Insert all attributes that have been defined.
//$filterList=['departement','famille','fournisseur'];
//foreach ($filterList as $item){
 //   $order[$item]= $order[$item];
//}


$filter=['designation'=>'designation','contenance'=>'contenance','uniteContenance'=>'uniteContenance',
    'conditionnement'=>'conditionnement','famille'=>'famille',
    'departement'=>'departement','fournisseur'=>'fournisseur','groupe'=>'groupe',
    'prixAchat'=>'prixAchat','tva'=>'tva',
    'uniteVente'=>'Unité de Vente','remarques'=>'remarques','refFour'=>'refFour','tri'=>'tri'];
$order['prixAchat']=str_replace(",",".",$order['prixAchat']);
/*if ($order['famille']!=""){
    echo "found";
    $order['departement']=$dico_fam_dep[$order['famille']];
}*/
//echo $order['departement'];
if ($order['uniteVente']=="on"){$order['uniteVente']="Kg";}else{$order['uniteVente']="U";};

if (isset($_REQUEST['new'])){
    //var_dump($_REQUEST);
    $record=true;
    $compulsoryKeyword=$filter;
    unset($compulsoryKeyword['remarques']);
    unset($compulsoryKeyword['tri']);
    unset($compulsoryKeyword['groupe']);
    //unset($compulsoryKeyword['departement']);
    unset($compulsoryKeyword['refFour']);
    
    //dispArray($compulsoryKeyword);
    //echo $order['fournisseur'];
    foreach ($compulsoryKeyword as $key=>$name){
        if ($order[$key]==""){
            $record=false;
            echo "Il manque $name.<br>";
        }
    }

    if ($record){
        //$request=["ean","designation","departement","famille","fournisseur",
        //"tva","reason","link","comments","prixVente",
        //"conditionnement","contenance","uniteContenance","uniteVente"];
        if ($order['tri']==""){$order['tri']=0;}
        //if ($order['departement']==""){$order['departement']=0;}
        
        if ($_REQUEST['ean']==""){$_REQUEST['ean']=new_valid_ean();
            $order['ean']=$_REQUEST['ean'];
            echo "Le code barre ".$_REQUEST['ean']." a été créé.";
        }
        $order['prixAchat']=str_replace(",",".",$order['prixAchat']);
        $request=array_keys($items);
        
        $filter["ean"]="ean";
        $filter["validated"]="validated";
        $order["validated"]=0;
        $attributeName="(";
        $attributeValue="(";
        $comma="";
        foreach (array_keys($filter) as $name){
            //echo $name."-";
            if (isset($order[$name])){
                //echo $name."<br>";
                $attributeName.=$comma.$name."";
                $attributeValue.=$comma."'".$order[$name]."'";
                $comma=",";
            }
        }
        $attributeName.=",author)";
        $attributeValue.=",'".$_SESSION['userInfo']['userId']."')";
        $query="INSERT INTO `prod_articles` ".$attributeName." VALUES ".$attributeValue;
        //echo "<br>".$query;
        simple_query($query);
        $query="INSERT INTO `prod_prices` (ean,thedate,prixAchat,source,author) 
        VALUES (".$order['ean'].",'".date("Y-m-d")."','".$order['prixAchat']."','creation',".$_SESSION['userInfo']['userId'].")";
        echo $query."<br>";
        simple_query($query);
        // treat fournisseur 2
        if (isset($_REQUEST['fournisseur2'])){
            if ($_REQUEST['fournisseur2']!=""){
                    $query="SELECT * FROM prod_article_fournisseur WHERE ean=".$order['ean'];
                    $table=query_table($query);
                    if (sizeof($table)==1){
                        $query="INSERT INTO prod_article_fournisseur (ean,fournisseur) 
                        VALUES (".$order['ean'].",".$order['fournisseur2'].")";
                        echo $query;
                        simple_query($query);
                    }
                }
        }
        
        //header("Location: ./0015-creationDeProduits.php?ean=".$_REQUEST['ean']);
    }
    else{
        echo "Pas encore assez de données pour pouvoir enregistrer le produit.<br>";
    }
}
else{
//var_dump($_REQUEST);
    //if ((isset($_REQUEST['change'])) && ($order['ean']!="")){
    if ($order['ean']!=""){
        //echo "wants to change";
        $ean=$order['ean'];
        //echo "ean=".$ean;
        $query="select * from prod_articles where ean =$ean";
        $dicoOneArticle=query_table_dico($query);
        if (sizeof($dicoOneArticle)>0){
            $dicoOneArticle=$dicoOneArticle[0];
            //dispArray($dicoOneArticle);

            foreach (array_keys($filter) as $keyword){
                //echo $keyword."=>order=".$order[$keyword]." dico=".$dicoOneArticle[$keyword]."<br>";
                //echo "is equal".((!($order[$keyword]=="")) && ($order[$keyword]!=$dicoOneArticle[$keyword]))."<br>";
                //echo "is equal1=".(intVal($order[$keyword]!=""))."<br>"  ;
                //echo "is equal2=".($order[$keyword]!=$dicoOneArticle[$keyword]);"<br>";
                
                if ((1-intval($order[$keyword]=="")) && ($order[$keyword]!=$dicoOneArticle[$keyword])){
                    $query="UPDATE prod_articles SET $keyword='".addslashes($order[$keyword])."' where ean='$ean'";
                    echo "<br>".$query."</br>";
                    simple_query($query);
                    $query="UPDATE prod_articles SET validated=0 where ean='$ean'";
                    echo "<br>".$query."</br>";
                    simple_query($query);
                    $query="INSERT INTO `prod_prices` (ean,thedate,prixAchat,source,author) 
                    VALUES (".$order['ean'].",'".date("Y-m-d")."',".$order['prixAchat'].",'creation',".$_SESSION['userInfo']['userId'].")";
        
                    simple_query($query);
                    echo "<div class='modified'>Mise à jour de $ean</div>";

                }
            }
            // treat fournisseur 2
            if (isset($_REQUEST['fournisseur2'])){
                if ($_REQUEST['fournisseur2']!=""){
                    $query="SELECT * FROM prod_article_fournisseur WHERE ean='".$order['ean']."'";
                    $table=query_table($query);
                    if (sizeof($table)==1){
                        $query="INSERT INTO prod_article_fournisseur (ean,fournisseur) 
                        VALUES (".$order['ean'].",".$order['fournisseur2'].")";
                        echo $query;
                        simple_query($query);
                    }
                }
            }
        }
        else{
            echo "Cet article n'existe pas encore. Choisissez nouveau produit.<br>";
        }
    }
}

//----------------------------------------------------------------------
// Retrieve suggested products and not validated
//
if ($_SESSION['userInfo']['admin']){$where="";}else{$where=" AND author=".$_SESSION['userInfo']['userId'];}

$listeProduitsQuery="SELECT * FROM prod_articles WHERE validated=0 $where ORDER BY TRI";
//echo $listeProduitsQuery;
$listeProduitsTable=query_table($listeProduitsQuery);
//displayTableInHtml($listeProduitsTable);
//----------------------------------------------------------------------
// créer les listes

// search items
//var_dump($dico);
$filterList=['departement','famille','fournisseur','groupe'];
$list=[];
foreach ($filterList as $item){
    $list[$item]= listeSelect($dico,$item,$item,$order[$item],1); // the 1 if for order
}
$item='fournisseur';
$list['fournisseur2']= listeSelect($dico,$item,'fournisseur2',$order['fournisseur2'],1); // the 1 if for order

// Display table of selected products
//$htmlHeader="<form method='post' enctype='multipart/form-data'>";
/*$htmlHeader="Fichier import AEMsoft<input type='file' name='importFile' id='importFile'></input>";
$htmlHeader.="<input type='date' name='importDate' value=".date("Y-m-d")."></input>";
$htmlHeader.="<button type='submit'>GO</button>";*/
$htmlHeader="";
$htmlChoix=$htmlHeader;
$htmlChoix.= "\n<div class='choix choose'>";
$currentId=0;
//$listeProduitsTable=[];
if (sizeof($listeProduitsTable)>1){
    $htmlChoix.= "\n<h3>Liste des articles récemment créés et non validés.</h3>";
    echo "<a href='0022-exportNouveauxArticles.php'>Export nouveaux articles</a>";
    $htmlChoix.= "\n<p>Vous pouvez en effacer en cliquant sur les croix rouges</p>";
    $htmlChoix.= "\n<div class='headerCreationProduit'><table class='creation'>\n";
    //var_dump($selected);
    $attrList=['ean','refFour','designation','tri','departement','famille','fournisseur','tva','prixAchat','uniteVente','link'];
    $attrNameList1=array('ean'=>'Code-barre','designation'=>"Désignation",'tri'=>"tri",'departement'=>"Département");
    $attrNameList2=array('famille'=>"Famille de Produit",'fournisseur'=>"Fournisseur",'tva'=>"TVA",'prixAchat'=>'Prix de Vente');
    $attrNameList3=array('reason'=>'Intérêt','link'=>'lien','refFour'=>'Référence Fournisseur','uniteVente'=>'v');
    $attrNameList=array_merge($attrNameList1,$attrNameList2,$attrNameList3);
    $htmlChoix.= "<tr>";
    $row=$listeProduitsTable[0];
    foreach ($attrList as $attr){
        $htmlChoix.= "<th class='$attr'>".$attrNameList[$attr]."</th>";

    }
    $htmlChoix.= "<th class='erase'></th><th class='valid'></th></tr>\n";
    $htmlChoix.= "</table>
    </div>";
    // Main
    $htmlChoix.= "<div class='creationProduit'>
    <table class='creation'>\n";
    //displayTableInHtml($tableReasons);
    for ($k=1;$k<sizeof($listeProduitsTable);$k++){
        $htmlChoix.= "<tr>";
        $row=$listeProduitsTable[$k];
        foreach ($attrList as $attr){
            if ($attr=="designation"){
                $htmlChoix.= "<td class='designation'><button type='button' name='ean' value='".$row['ean']."'>".$row[$attr]."</button></td>";
            }
            else{
                $htmlChoix.= "<td class='$attr'>".$row[$attr]."</td>";
            }
        // identify next article
        if ($row['ean']==$order['ean']){
            $currentId=$k;
        }
        //
        }
        $htmlChoix.= "<td class='erase'><img src='../0101-images/redCross.png' class='imgIcon' onclick='erase(".$row['ean'].")')></img></td>";
        $htmlChoix.= "<td class='valid'><img src='../0101-images/greenChecked.png' class='imgIcon' onclick='validate(".$row['ean'].")')></img></td>";
        $htmlChoix.= "</tr>\n";
    }
    $htmlChoix.= "</table>
    </div>";
    // Next id
    $nextId=$currentId+1;
    if ($nextId>=sizeof($listeProduitsTable)){
        $nextId=1;
    }
}
else{
    echo "<div><h3>Tous les articles sont validés.</h3></div>";
}
echo "</div>";

//----------------------------------------------------------------------
//  Next Product
//
//var_dump($_REQUEST);
if (isset($_REQUEST['nouvSuivant'])){
    if ($_REQUEST['nouvSuivant']!=""){
        //echo "<h3>Suivant</h3>";
        //echo "next is ".$nextId;
        $order['ean']=$listeProduitsTable[$nextId]['ean'];
        //echo "next is ".$nextId."ean=".$order['ean'];
    }
}

//----------------------------------------------------------------------
// Main form
// Form to add an item
//
//----------------------------------------------------------------------
$htmlNouveau= "<div class='ajout'>";
$htmlNouveau.= "Créer ou modifier un produit\n";
$htmlNouveau.="<table id='creationTable'>
    </tr><td>EAN</td><td><input id='ean' name='ean' value='".$order['ean']."'></input></td><td></td></tr>";
$htmlNouveau.="</tr><td>Référence Fournisseur</td><td>
    <textarea id='refFour' name='refFour'>".$order['refFour']."</textarea></td>";

//----------------------------------------------------------------------
// revue des alias fournisseur
    $htmlNouveau.= "<td>"; 
    //if ($order['refFour']!=""){
    $query="SELECT * FROM prod_refFour_alias where refFour='".$order['refFour']."'";
    $table=query_table($query,1);
    $refFourOneDico=create_product_dictionnary($table,'refFourAlias');
    //var_dump($refFourOneDico);
    $htmlNouveau.= "<div class='aliases'>";
    $htmlNouveau.= "refFour alias<input name='refFourAlias'></input>";
    //echo "<input name='ean' value='".$order['refFour']."' type='hidden'></input>";
    $htmlNouveau.= "<button type='submit' name='addAlias'>Ajouter</button>";
    $htmlNouveau.= "<table id='refFourAlias' class='alias'>";
    foreach ($refFourOneDico as $row){
        //dispArray($row);
        $htmlNouveau.= "<tr>
            <td><input name='edit[]' value=".$row['refFourAlias']."></input></td>
        </tr>";
    }
    $htmlNouveau.= "</table>";
    $htmlNouveau.= "</div>";
    $htmlNouveau.= "</td>";
    //}
        //displayinhtml($table);
//-- fin revue alias
//-------------------------------

  
$htmlNouveau.= "</td></tr>" ;   
$htmlNouveau.="</tr><td>no tri dans la liste</td><td>
    <input id='tri' name='tri' value=".$order['tri']."></input></td><td></td></tr>" ; 
$htmlNouveau.="</tr><td>Désignation</td><td>
    <textarea id='designationText' name='designation'>".strtoupper($order['designation'])."</textarea></td><td></td></tr>";

// CONTENANCE
$item="contenance";
//echo $order['uniteContenance'];
$uniteContenanceMatch=[1=>'U/kg/l',2=>'unité',3=>'Litre',5=>"La pièce",4=>'kg'];

$checked=["","","","",""];
if ($order['uniteContenance']!=""){
    $checked[$order['uniteContenance']-1]="selected";
}
//var_dump($checked);
$htmlNouveau.="
    <tr><td>".$items[$item]."</td><td>
    <input id='$item' name='$item' value=".$order[$item]."
    ></input>
    <select id='uniteContenance' name='uniteContenance'>
        <option value='1' ".$checked[0].">U/kg/l</option>
        <option value='2' ".$checked[1].">unité</option>
        <option value='3' ".$checked[2].">Litre</option>
        <option value='4' ".$checked[3].">kg</option>
        <option value='5' ".$checked[4].">La pièce</option>
    </select>
    </td><td></td></tr>";

$htmlNouveau.="
    <tr><td>Conditionnement</td><td>
    <input id='conditionnement' name='conditionnement' value=".$order['conditionnement']."
    ></input>
    </td><td>quantité par colis</td></tr>";
$htmlNouveau.="
    <tr><td>Département</td><td>
    ".$list['departement'].
    "</td><td></td></tr>";
$htmlNouveau.="<tr><td>Famille</td><td>
    ".$list['famille'].
    "</td></tr>";
$htmlNouveau.="<tr><td>Fournisseur</td><td>
    ".$list['fournisseur'].$list['fournisseur2'].
    "</td>";

    
$htmlNouveau.="</tr>"; 
    
// Vente
$checked="";
if ($order['uniteVente']=="Kg"){    $checked=" checked ";}

$htmlNouveau.="<tr><td>Prix HT</td>
    <td id='prixAchatDiv'>
    <div >
    <input id='prixAchat' name='prixAchat' value=".$order['prixAchat']." >
       </input>
    </div>
    <div id='uniteVente'>Unité </div>
    <div class='form-check form-switch'>
        <input class='form-check-input' type='checkbox' 
        id='flexSwitchCheckDefault' name='uniteVente' 
        $checked />
    </div>
    <div>kg</div>
    </td><td></td></tr>"; 
//$htmlNouveau.="<tr><td>Marque</td><td>
//<input  name='marque' value=".$order['marque']." ></input> defaut 16.6667
//    </select>";
 //   "</td><td></td></tr>"; 

/*$item="uniteVente";
$htmlNouveau.="
    <tr><td>".$items[$item]."</td><td>
    <input id='$item' name='$item' value=".$order[$item]."
    ></input>
    </td><td>quantité par colis</td></tr>";*/
$htmlNouveau.="<tr><td>TVA</td><td>
    <select id='tva' name='tva' size='1' >
        <option value=1>5.5%</option>
        <option value=2>20%</option>
    </select>
    </td></tr>"; 
    
$htmlNouveau.="<tr><td>Groupe</td><td>
    ".$list['groupe'].
    "</td></tr>";
        
$htmlNouveau.="<tr><td>Date Limite de Consommation</td><td>
    <input  name='dlc' value=".$order['dlc']." ></input> jours
    </select>";
    "</td><td></td></tr>"; 
$item="link";
$htmlNouveau.="</tr><td>".$items[$item]."</td><td>
    <input name='link' >
    </td></tr>";

$htmlNouveau.="</tr><td>Remarques</td><td>
    <textarea rows=4 cols=50 name='remarques' ></textarea>
    </td></tr>";

$htmlNouveau.="
    </table>
    </div>
    </div>";

//----------------------------------------------------------------------
// Create the body of the page
//----------------------------------------------------------------------

//echo "<h1>En travaux</h1>";
echo "<form id=formAdd method='post' enctype='multipart/form-data'>";
    echo "<div class='topBanner'>";
        echo "<div class='center'>";
        
            echo $htmlChoix;
        echo "</div>";
    echo "<div class='clr'></div>";
    echo "<div><button name='nouvSuivant'>Suivant Nouveau</button></div>";
	 
    
    
    echo "<button name='change' >Modifier</button>
    <button name='new'>Nouveau Produit</button>";

   if ($_SESSION['userInfo']['admin']){
		echo "<button name='eanAlias'>ean Alias</button>";
		echo "<button name='refForAlias'>refFour Alias</button>";
	}   
    echo $htmlNouveau;




//------------------------------------------------------------------------------------------
// La liste
    $str="<table>
    <tr><th>ean</th><th>refFour</th><th>designation</th></tr>
    <tr id='search' >
        <td><input id='eanSearch' name='eanSearch' value='".$order['eanSearch']."' ></input></td>
        <td><input id='refFourSearch' name='refFourSearch' value='".$order['refFourSearch']."'></input></td>
        <td><input id='designationSearch' name='designationSearch' value='".$order['designationSearch']."'></input></td>
    </tr>
    </table>"; 

//----------------------------------------------------------------------
// Liste des produits
//
    $str.="<div id='itemsList'>";
    echo $str;
    //$str.=createListe($listeProduitsQuery,'ean',$total,$filter,$special,$order);

echo "</div>";
echo "<input  type='hidden' name='commande' value='".$_SESSION['commande']."'></input>";
echo "</form>";
// Fin de la liste


?>
<script>
$( document ).ready(function() {
    
    //$('#add').submit(function(event){
    //    event.preventDefault();
    //});
    //$(document).on('submit', '#add', function(event){
    //    event.preventDefault();
    //});
    $('#add').on('click',"button[name='change']",function(){
        console.log("button Modifier clicked");
        $("#add").submit();
    });
    $('#add').on('click',"button[name='new']",function(){
        console.log("button New clicked");
        $("#add").submit();
    });
    
    //$('.theCommand').on('keydown','input[name="prixAchat[]"]',function(e){
    //        console.log(e.keyCode);
     //       if(e.keyCode==13){
     //           event.preventDefault();
    
    $("#creationTable").on("change","select",function(event){
        console.log("select validated");
        event.preventDefault();
    });
    
    //------------------------------------------------------------------
    // deals with ean
    //------------------------------------------------------------------
    
    function checkEan(e){
        console.log("ean to be validated");
        if(e.keyCode==13){
            event.preventDefault();
            displayEanMessage();
            $("#designationText").focus();
            
            /*var inputs = $("#creationTable").find("textarea");
            if (inputs[inputs.index(this) + 1] != null) {  
                    console.log("index is "+ (inputs.index(this) ));                 
                    inputs[inputs.index(this) + 1].focus();
                }
            */
            
            return false;
        }
        
    }
    function displayEanMessage(){
        if (isValidEAN($("#ean").val())){
                console.log("ok");
                $("#ean").parents("tr").children().eq(2).html("code ean13 valide");
                $("#ean").parents("tr").children().eq(2).addClass("inputGreen");
                $("#ean").parents("tr").children().eq(2).removeClass("inputRed");
        }
        else{
            console.log("wrong ean");
            $("#ean").parents("tr").children().eq(2).html("code ean13 incorrect");
            $("#ean").parents("tr").children().eq(2).addClass("inputRed");
            $("#ean").parents("tr").children().eq(2).removeClass("inputGreen");
        }
        
        
        if ($("#ean").val()!=""){
            //
            // look for article
            //
            query="SELECT A.*,B.fournisseur as fournisseur2 FROM (SELECT * FROM prod_articles WHERE ean='"+$("#ean").val()+"') as A LEFT OUTER JOIN prod_article_fournisseur as B on A.ean=B.ean";
            console.log(query);
            $.ajax({
                url : '../0021-functions/0406-retrieve_data.php', // La ressource ciblée
                type:'POST',
                data: { query: query},
                success: function(response){ 
                    console.log(response);
                    var data = JSON.parse(response);
                    
                    //$("#tableContain").show();
                    $("#designationText").html(data.designation.toUpperCase());
                    console.log($("#contenance").val());
                    $("#contenance").val(data.contenance);
                    //console.log("unite contenance"+data.uniteContenance);
                    //$("#uniteContenance").val(data.uniteContenance);
                    $('#uniteContenance').children("option").each(function (){
                        $(this).attr('selected',false);
                    });
                    
                    $('#uniteContenance>option:eq('+(data.uniteContenance-1)+')').attr('selected', true);
                    $("#conditionnement").val(data.conditionnement);
                    $("#tri").val(data.tri);
                    $("#famille").val(data.famille);
                    $("#departement").val(data.departement);
                    $("#groupe").val(data.groupe);
                    
                    $("#refFour").html(data.refFour);
                    $("#fournisseur").val(data.fournisseur);
                    $("#fournisseur2").val(data.fournisseur2);
                    $("#prixAchat").val(data.prixAchat);
                    //$("#marque").val(data.marque);
                    if (data.uniteVente=='U'){
                        console.log("unchecked")
                        $("#flexSwitchCheckDefault").attr("checked",false);
                    }
                    else{
                        $("#flexSwitchCheckDefault").attr("checked",true);
                        console.log("checked")
                    }
                    $("#uniteVente").val(data.uniteVente);
                    // change...
                    //console.log("tva="+data.tva);
                    $("#tva").val(data.tva);
                    //$('#tva>option:eq('+(data.tva-1)+')').attr('selected', true);
                    $("#dlc").val(data.dlc);
                    $("#link").val(data.link);
                    $("#commentaires").val(data.commentaires);
                                    
                    //$(this).html(response);
                    //console.log(response);
                    //console.log("ean updated");
                    //
                    // refFOUR
                    //console.log("refFour="+$("#refFour").parent().html());
            
                    query="SELECT * FROM prod_refFour_alias WHERE refFour='"+$("#refFour").html()+"'";
                    console.log("refFour: "+query);
                    $.ajax({
                        url : '../0021-functions/0407-return_table_data.php', // La ressource ciblée
                            type:'POST',
                        data: { query: query},
                        success: function(response){ 
                            if (response!=""){
                                $('#refFourAlias').html(response);
                            }
                            else{
                                $('#refFourAlias').html("");
                            }
                            //$(this).html(response);
                            console.log(response);
                        }
                    });
                }
            });
                    
                    
                    
                    
                    
                    
      
        //}
        //if ($("#ean").val()!=""){
            
            //
            // look for refFour alias
            //
            
                
                
        }
                
                
                
    $('.creation').on('click','button[name="ean"]',function(){
       console.log(ean);    
       ean=$(this).parents("tr").find(".ean").html();
       console.log(ean);
       $("#ean").val(ean);
       displayEanMessage();
       //formAdd.submit();
    });



    $('#itemsList').on('click','button[name="ean"]',function(){
       console.log(ean);    
       ean=$(this).parents("tr").find(".ean").html();
       console.log(ean);
       $("#ean").val(ean);
       displayEanMessage();
       //formAdd.submit();
    });    
    }
    // treat ean
    $("#creationTable").on("keydown","#ean",function(e){
        console.log("entered ean");
        checkEan(e);
    });
    displayEanMessage(); // check ean
    
    
    function checkEan(e){
        console.log("ean to be validated");
        if(e.keyCode==13){
            event.preventDefault();
            displayEanMessage();
        }
    }
    //------------------------------------------------------------------
    // deals with prixTTC
    //------------------------------------------------------------------
    $("#creationTable").on("keydown","#prixAchat",function(e){
        console.log("ean to be validated");
        if(e.keyCode==13){
            event.preventDefault();
            displayPrixAchatMessage();
        }
    });
    function displayPrixAchatMessage(){
        if (checkNumber($("#prixAchat").val())){
            console.log("prixAchat ok");
            var cell=$("#prixAchat").parents("tr").children().eq(2);
            cell.html("prix d'Achat valide");
            cell.addClass("inputGreen");
            cell.removeClass("inputRed");
                
        }
        else{
            console.log("prixAchat wrong");
            var cell=$("#prixAchat").parents("tr").children().eq(2);
            cell.html("le prix proposé n'est pas un nombre");
            cell.addClass("inputRed");
            cell.removeClass("inputGreen");
        }
    }
    
    // display tri column
    $('#itemsList').on('click','#tridisp',function(){
        console.log("tridisp clicked");
        console.log($('#tridisp').attr("class"));
        $('#tridisp').toggleClass("clicked");
        $('#tri').attr("class","icon tri");
        filteredList();
        
    });
    
    
    
    
});
    
function checkNumber(nb){
    nb=nb.replace(",",".");
    nb=parseFloat(nb);
    console.log(nb);
    if (typeof nb !== 'number') {
        console.log(typeof nb);
        return false
    }
    // NaN is the only JavaScript value that never equals itself.
    if (nb !== Number(nb)) {
        return false
    }

    return true   
}
  
function erase(theean){
    query='DELETE FROM prod_articles WHERE ean="'+theean+'"';
    if (confirm("Etes vous-sûr d'effacer "+theean+"?")){
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: { query: query},
                    success: function(response){ 
                                        
                        $(this).html(response);
                        console.log(response);
                    } 
            });
    }
    formAdd.submit();
}
 
function validate(theean){
    query='UPDATE prod_articles SET validated=1 WHERE ean="'+theean+'"';
    console.log(query);
    //if (confirm("Etes vous-sûr de valider l'article "+theean+"?")){
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: { query: query},
                    success: function(response){ 
                                        
                        $(this).html(response);
                        console.log(response);
                    } 
            });
    //}
    formAdd.submit();
}
  
/**
 * Test a string for valid EAN5 EAN8 EAN13 EAN14 EAN18
 * @see: https://www.activebarcode.com/codes/ean13.html
 * @param  {string} ean A string to be tested
 * @return {boolean} true for a valid EAN
 * @author Vitim.us <https://stackoverflow.com/a/65928239/938822>
 */
 
function isValidEAN(ean) {
    function testChecksum(ean) {
        const digits = ean.slice(0, -1);
        const checkDigit = ean.slice(-1) | 0;
        let sum = 0;
        for (let i = digits.length - 1; i >= 0; i--) {
            sum += (digits.charAt(i) * (1 + (2 * (i % 2)))) | 0;
        }
        sum = (10 - (sum % 10)) % 10;
        return sum === checkDigit;
    }
    ean = String(ean);
    const isValidLength = ean.length === 18 || ean.length === 14 || ean.length === 13 || ean.length === 8 || ean.length === 5;
    return isValidLength && /^\d+$/.test(ean) && testChecksum(ean);
}




function filteredList(){
        vean=$("#eanSearch").val();
        vdesign=$("#designationSearch").val();
        vrefFour=$("#refFourSearch").val();
        vfournisseur=$("#fournisseur").val();
        vfamille=$("#famille").val();
        vdepartement=$("#departement").val();
        if (vfamille==""){familleStr="";}else{familleStr=" AND famille='"+vfamille+"'";}
        if (vfournisseur==""){fournisseurStr="";}else{fournisseurStr=" AND fournisseur='"+vfournisseur+"'";}
        if (vdepartement==""){departementStr="";}else{departementStr=" AND departement='"+vdepartement+"'";}
        if ($('#tri').attr("class")=="icon tri"){mytri=" tri,designation"}else{mytri=" designation"}
        
        //console.log(vdepartement.value);
        //alert(fournisseur);
        // take all articles even old ones if not change $where="where validated=1"
        $where="";
        query="SELECT * FROM (SELECT * FROM prod_articles $where) as prod_articles WHERE ean like '%"+vean+"%' "+departementStr+" AND designation like '%"+vdesign.toUpperCase()+"%' "+familleStr+"  AND refFour like '%"+vrefFour+"%' "+fournisseurStr+" order by "+mytri;
        //alert(query);
        console.log(query);
        
        // modified: POST
        $.ajax({
                //url : '403-produitEditListjquery.php', // La ressource ciblée
                url : '0400-createListeforListeDesProduits.php', // La ressource ciblée
                type:'POST',
                data: { query: query,               
                        tridisp:$("#tridisp").attr("class")
                     },
                success: function(response){  
                    //console.log("response="+response);                 
                    $("#itemsList").html(response);
                } 
                
            //alert(response);
        });
        //$("tr").removeClass("theList");
        //$("#searchList").addClass("editList");
        console.log("Activate img after filtered list");
        
        //activateSearch();
    }
    filteredList();

    $(".leftNav").find("div").eq(5).addClass('navSelected');
$('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#creation");
})
</script>
  




