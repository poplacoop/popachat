<?php
@session_start();
$_SESSION['listeDesProduits']="listeDesProduits";
//var_dump($_SESSION);

include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0501-graphFunctions.php";

//----------------------------------------------------------------------
// Create the body of the page
//----------------------------------------------------------------------
//----------------------------------------------------------------------
// start html
//----------------------------------------------------------------------
// prepare menu

echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);


//var_dump($_REQUEST);
//echo $_SESSION['graph'];
//-----------------------------------------------------------------------
// Insert New Item in commandeList
$htmlNoInsert="";
// ajout d'une valeur
if ($order['ean']!=""){
	echo "ajout  d'une valeur";
	$tablekey=[];
	$tablekey['ean']=$order['ean'];
    $query="select * from prod_commandeList where commande_id=".$order['commande']." and ean='".$order['ean']."';";
    echo $query;
    $itemTable=query_table($query);
    //displayinhtml($itemTable);
    if (sizeof($itemTable)>1){
        $htmlNoInsert="<div>L'article ".$produitsDico[$tablekey['ean']]['designation']." est déjà dans la commande</div>"; 
    }
    else{
        
        //$tablekey['designation']=$_REQUEST['designation'];
        $tablekey['prixAchat']=$produitsDico[$tablekey['ean']]['prixAchat'];
        //$tablekey['quantite']=$_REQUEST['quantite'];
        $tablekey['commande_id']=$order['commande'];
        $filter=['ean','prixAchat','commande_id'];
        $query=create_INSERT('prod_commandeList',$tablekey,$filter,$order['commande']);
        //echo $query;
        simple_query($query);
    }
}
// modification d'une valeur
//var_dump($order['eanModif']);
//var_dump($_REQUEST);
if (($order['eanModif']!="")&&($order['myRange']=="")){
	echo "modification d'une valeur";
	$tablekey=[];
	$tablekey['ean']=$order['eanModif'];
    $query="select * from prod_commandeList where commande_id=".$order['commande']." and ean='".$order['eanModif']."';";
    echo $query;
    $itemTable=query_table($query);
    //displayinhtml($itemTable);
    if (sizeof($itemTable)>1){
        echo "modification";
        if ($order['quantite']!=""){
            $query="update prod_commandeList SET quantite='".$order['quantite']."' where id='".$itemTable[1]['id']."';";
            echo $query;
            simple_query($query);
        }
        
        
    }
    else{
        
        $htmlNoInsert("problem with quantite modification");
    }
}

//----------------------------------------------------------------------
// Retrieve DATA
//----------------------------------------------------------------------
$cmdInfo="";
if ($order["commande"]!=""){
    $commandeQuery="SELECT * FROM prod_commande WHERE id='".$order["commande"]."' order by date_livraison_prevue";
    $commandeTable=query_table($commandeQuery);
    //var_dump($commandeTable);
    //displayTableInHtml($commandeTable);
    $cmdInfo=$commandeTable[1];
    $order['fournisseur']=$cmdInfo['fournisseur'];
    $_SESSION['fournisseur']=$order['fournisseur'];
    //var_dump($cmdInfo);
}

$where="";
if ($order['listedepartement']!=""){
    $where=" and departement=".$order['listedepartement'];
}
if ($order['listefamille']!=""){
    $where.=" and famille=".$order['listefamille'];
}
if ($order['fournisseur']){
    $where.=" and fournisseur='".$order['fournisseur']."'";
}

$listeProduitsQuery="SELECT * FROM (SELECT * FROM prod_articles where validated=1) as prod_articles where 1 ".$where;
//echo $listeProduitsQuery;
$listeProduitsTable=query_table($listeProduitsQuery);
$produitDesignationDico=create_one_field_dictionnary($listeProduitsTable,"ean","designation");



$editArticleListe=['ean','refFour','designation','fournisseur','departement','famille','conditionnement','unite','contenance','tva','prixAchat','prixVente'];
    
// Update article
/*if (isset($_REQUEST['edit'])){
    //taken from function ListFullBis()
    //$attributeList=['ean','refFour','departement','tva','famille','designation','conditionnement','contenance','unite','prixAchat'];//,'p vente','stock','stock','fournisseur'];
    //var_dump($_REQUEST['edit']);
    $row=0;
    $nbAttribute=sizeof($editArticleListe);
    $nbRow=floor(sizeof($_REQUEST['edit']));
    while (isset($_REQUEST['editEan'][$row])){
        $ean=$_REQUEST['editEan'][$row];
        $edit=$_REQUEST['edit'];
        echo $ean."<br>";
        for ($i=1;$i<$nbAttribute;$i++){
            if (isset($edit[$i-1+$row*($nbAttribute-1)])){
                //echo $i.$editArticleListe[$i];
                //echo $edit[$i-1+$row*$nbAttribute];
                $query="update prod_articles set ".$editArticleListe[$i]."='".$edit[$i-1+$row*($nbAttribute-1)]."' where ean='".$ean."';";
            
                //echo $query."<br>";
                simple_query($query);
            }

        }
        $row++;
    }
    //header("Location:0-generalHeader.php?fournisseur=".$order['fournisseur']."&commande=".$order['commande']);
}
*/
$insertMsg="";

// Liste des produits de la commande
$htmlList= "<div id='searchList'>";
//include "408-produitEditList.php";

//$htmlList.=$str;
$htmlList.= "</div>";

//----------------------------------------------------------------------
// get information from selected commande
if($order['commande']!=""){
    $commandeQuery="SELECT ean,quantite from prod_commandeList WHERE commande_id=".$order['commande'];
    //echo $commandeQuery."<br>";
    //$query="select list.*,cmd.quantite from ($listeProduitsQuery) as list left outer join ($commandeQuery) as cmd on list.ean=cmd.ean";
    //echo $query."<br>";
    $commandeTable=query_table($commandeQuery);
    $commandeDico=create_one_field_dictionnary($commandeTable,"ean","quantite");
    $nb=sizeof($commandeTable)-1;
    
}
else{
     $query=$listeProduitsQuery;  
}




//----------------------------------------------------------------------
// title and selected fournisseur and items
//echo "graph=".$order['graph'];
echo "<h1>Liste des Produits</h1>";
if ($order['fournisseur']){
    echo "<div class='chosen'>".$order['fournisseur']."-".$dico['fournisseur'][$order['fournisseur']]."</div>";
    echo "<input type='hidden' name='fournisseur', id='fournisseur' value='".$order['fournisseur']."'></input>";
}
else{
    echo "<div class='chosen '>Fournisseur non sélectionné </div>";
    echo "<input type='hidden' name='fournisseur', id='fournisseur' value=''></input>";
    $selectedId="";
}
if ($order['commande']){
    $selectedId=$order['commande'];
    echo "<div class='chosen'>no ".$order['commande']." pour le ".$cmdInfo['date_livraison_prevue']." avec $nb articles</div>";
    echo "<input type='hidden' name='commande', id='commande' value='".$order['commande']."'></input>";
}
else{
    echo "<div class='chosen'> Commande non sélectionnée</div>";
    echo "<input type='hidden' name='commande', id='commande' value=''></input>";
    $selectedId="";
}
echo $htmlNoInsert;

//----------------------------------------------------------------------
// begin form
$str="<form name='myForm' method='post'>";
//----------------------------------------------------------------------
// range for history and graph
$str.= "<div><div id='theRange'>";
$str.=" <span class='text' >Somme des ventes sur </span>";

if ($order['graphSales']=="on"){$checked="checked";}else{$checked="";}



$str.="<div id='theRange'>";
$str.= "<input id='myRange' name='myRange' value='".$order['myRange']."'></input>";
$str.= "<div class='text'>semaines.</div>";
$str.= "<input type='range' class='form-range' min='0' max='12' value='".$order['myRange']."' step='1' id='myRangeBar'/>";
$str.="</div>";



/*
$str.= "<input type='range' class='form-range' min='0' max='12' value='".$order['myRange']."' step='1' id='myRangeBar'>";
$str.= "<input name='myRange' value='".$order['myRange']."'></input>";
$str.= "<div class='text'>semaines.</div>";
/*$str.="
<div class='form-check form-switch'>
  <input class='form-check-input' type='checkbox' id='flexSwitchCheckDefault' name='graph' $checked onchange='submit();'/>
</div>";*/
$str.="</div></div>";


// search items
$filterList=['departement','famille'];
$str.="<div>Filtres</div>";
foreach ($filterList as $item){
    if ($order['fournisseur']){
        $where.=" and fournisseur=".$order['fournisseur'];
    }
    $query="SELECT $item from prod_articles where validated=1 $where";
    $query="SELECT * from ($query) as a group by $item";
    $query="SELECT * from ($query) as sel left outer join prod_$item as list on sel.$item=list.id";
    $itemTable=query_table($query);
    $filter[$item]=create_one_field_dictionnary($itemTable,"id","titre");
    //displayinhtml($itemTable);
    //echo "item=".$item." ";
    $str.= listeSelect($filter,$item,"liste".$item,$order['liste'.$item],1); // the 1 if for order
}

if (!$order['fournisseur']){
    $str.= listeSelect($dico,"fournisseur","fournisseur",$order['fournisseur'],1); //0 is for no empty line added.
}
//var_dump($_REQUEST);
//echo "graph=".$order['graph'];
//echo "<script>
//graph=$(\"input[name='graph']\").val();
//        console.log(\"graphRun=\"+graph);
//</script>";
//----------------------------------------------------------------------
// create liste
$total=0;
//$filter=["ean","refFour","designation","colis"];
//$filter=['ean','refFour','designation','conditionnement'];
//$special=['quantite','prixAchat','stock','erase','editQuantite','search','ventes'];
//if ($order['graph']!=""){$special=array_merge($special,['graph']);}
        $str.="<table>
        <tr><th>ean</th><th>refFour</th><th>designation</th></tr>
        <tr id='search' >
            <td><input id='eanSearch' name='eanSearch' value='".$order['eanSearch']."' ></input></td>
            <td><input id='refFourSearch' name='refFourSearch' value='".$order['refFourSearch']."'></input></td>
            <td><input id='designationSearch' name='designationSearch' value='".$order['designationSearch']."'></input></td>
            <td><a id='exportXls' href='./0017-stockExcel.php' onclick='exportXls();' target='_blank'>ExportExcel</a></td>
        </tr>
        </table>"; 


$str.="<div id='itemsList'>";
//$str.=createListe($listeProduitsQuery,'ean',$total,$filter,$special,$order);

echo "</div>";
$str.="</form>";
echo $str;


echo "</div>";
//var_dump($order);
?>
<script>

$(document).ready(function() {
    function activateImg(){
        //alert("activate");
        // change quantite into input
        $('#itemsList').on('click','.imgQuantite',function(){
            console.log("quantite");
            
            $('#itemsList .chosen').each(function( index ) {
            console.log( index);
            //console.log($(this).children("td").children(".imgQuantite").attr("myid"));
            id=$(this).children("td").children(".imgQuantite").attr("myid");
            console.log(id);
            cell=$(this).children(".quantite"); // quantite
            //console.log($(this).attr("class"));
            
            if (cell.find('input').length==0){
                val=cell.html();
                cell.html("<input  name='quantite[]' value="+cell.html()+">");
                cell.append("<input  type='hidden' name='eanModif[]' value="+id+">");
                //cell.append("<input  name='quantite[]' value="+cell.html()+">");
            }
            else{
                console.log("effacer input");   
            }
            //console.log(cell.html());
            });
            
        });
        $('#itemsList').on('click','.imgEdit',function(){
        //$('img.imgEdit').click(function() {
            console.log("clicked");
            //id=$(this).prop('class').split(" ")[1];
            id=$(this).attr("myid");
            console.log("id="+id);
            
            cell=$(this).parents().parents().children().eq(0);
            if (cell.find('input').length==0){
                console.log("html="+cell.html());
                console.log("length="+cell.find('input').length);
                console.log(cell.val());
                cell.append("<input  type='hidden' name='editEan[]' value="+id+">");
            }
            console.log("next");
            for (i=1;i<11;i++){
                console.log(i);
                cell=$(this).parents().parents().children().eq(i);
                console.log(i+cell.html());
                if (cell.find('input').length==0){
                    val=cell.html();
                    cell.html("");
                    cellName=cell.attr("class");
                    cell.append("<input  name='edit[]' value="+val+">");
                }
            }

            
            /*for (i=0;i<1;i++){
                cell=$(this).parents().parents().children().eq(i);
                val=cell.html();
                //cell.append("<input  type='hidden' name='editId[]' value="+id+"></input><input name='edit[]' value='"+val+"' onenter='submit();' ></input>");
                val=$(this).parents().parents().children().eq(i).html();
                
                console.log($(this).find('input').length)
                console.log(val);
                //$(this).parents().parents().children().eq(i).html("<input  type='hidden' name='editId[]' value="+id+"></input><input name='edit[]' value='"+val+"' onenter='submit();' ></input>");
            }*/
                
            
            //var htmlString=$(this).parents().html()
            //alert(htmlString);
        });
        // change quantite in database
        $('#itemsList').on('change','input[name="quantite[]"]',function(){
            if ($('#commande').val()!=""){
                console.log("quantite nouvelle ");
                console.log($(this).val());
                console.log($(this).next().val());
                cond=$(this).parents("tr").find(".conditionnement").html();
                colis=Math.floor($(this).val()/cond+0.5);
                quantite=colis*cond;
                if ($(this).val()!=quantite){alert("Attention: la quantité a été modifié car incompatible avec le conditionnement");}
                console.log("conditionnement"+$(this).parents("tr").find(".conditionnement").html());
                //query="update prod_commandeList set quantite="+quantite+" where ean="+$(this).next().val();
                $(this).val(quantite);
                $(this).parents("tr").find(".colis").html(colis);
                query="update prod_commandeList set quantite="+quantite+" where ean="+$(this).parents("tr").find(".ean").html()+" and commande_id="+$('#commande').val();
                $(this).val(quantite);
                console.log(query);
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
            }
            event.preventDefault();
            return false;    
        });
        /*
        $('#searchList').on('enter','.imgEdit',function(){
            submit();
        });
        */
        
        /*
        $('img.imgEdit').click(function() {
            console.log("clicked");
            //id=$(this).prop('class').split(" ")[1];
            id=$(this).attr("myid");
            console.log(id);
            for (i=0;i<13;i++){
                val=$(this).parents().parents().children().eq(i).html();
                console.log(val);
                $(this).parents().parents().children().eq(i).html("<input  type='hidden' name='edit[]' value="+id+"></input><input name='edit[]' value='"+val+"' onenter='submit();' ></input>");
            }
                
            
            //var htmlString=$(this).parents().html()
            //alert(htmlString);
        });*/
        
        //console.log($('#choix').children().first().children().first().html());
        
        $('#itemsList').on('click','.imgErase',function(){
        //$('.imgErase').click(function() {
            console.log("imgErase");
            id=$(this).attr("myid");
            
            $(this).parents().first().parents().first().removeClass('chosen');
            erase(id);
        });
        console.log("activate img done");
    }
    
    function erase(id){
        query='DELETE FROM prod_commandeList WHERE id='+id;
        
        //alert(query);
        //alert('Ne pas oublier de rafraichir la page');
            // modified: POST
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
            type:'POST',
            //async: false,
            data: { query: query},
            success: function(response){ 
                                
                //$(this).html(response);
                console.log(response);
            } 
        });

    };
    

    function filteredList(){
        vean=$("#eanSearch").val();
        vdesign=$("#designationSearch").val();
        vrefFour=$("#refFourSearch").val();
        vfournisseur=$("#fournisseur").val();
        vfamille=$("#listefamille").val();
        vdepartement=$("#listedepartement").val();
        if (vfamille==""){familleStr="";}else{familleStr=" AND famille='"+vfamille+"'";}
        if (vfournisseur==""){fournisseurStr="";}else{fournisseurStr=" AND fournisseur='"+vfournisseur+"'";}
        if (vdepartement==""){departementStr="";}else{departementStr=" AND departement='"+vdepartement+"'";}
        console.log(vdepartement.value);
        //alert(fournisseur);
        query="SELECT * FROM (SELECT * FROM prod_articles where validated=1) as prod_articles WHERE ean like '%"+vean+"%' "+departementStr+" AND designation like '%"+vdesign.toUpperCase()+"%' "+familleStr+"  AND refFour like '%"+vrefFour+"%' "+fournisseurStr+" ORDER BY tri;";
        //alert(query);
        console.log(query);
        myRange=$("input[name='myRange']").val();
        graph=<?php echo "'".$order['graphSales']."'"; ?>;
        //$("input[name='graph']").val();
        console.log("graph="+graph);
        // modified: POST
        $.ajax({
                //url : '403-produitEditListjquery.php', // La ressource ciblée
                url : '0400-createListeforListeDesProduits.php', // La ressource ciblée
                type:'POST',
                data: { query: query,
                        graph:graph,
                        myRange:myRange
                        
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
        activateImg();
        //activateSearch();
    }
    //filteredList();
    
    
   $('#init').click(function() {
        console.log('init');
        $('#ean').val('');
        $('#refFour').val('');
        $('#departement').val('');
        $('#tva').val('');
        //$('#design').val('');
        $('#famille').val('');
        $('#radio input').removeAttr('checked');
        //filteredList();
        submit();
        return false;
    });
    

    //filteredList();
    $("#searchList").first().removeClass("theList");
    //alert($("#searchList").first().attr("class"));

    $("#searchList").first().addClass("editList");
    
    
   /* $("#export").click(function() {
        //$("#export").val(1-$("#export").val());
        //alert($("#export").val());
        
    });*/
    function activateSearch(){
        //$('#itemsList').on('keyup','#eanSearch',function(){
        $('input[name ="eanSearch"]').keyup(function() {
            console.log("searchClick");
            filteredList();
        }); 
        //$('#itemsList').on('keyup','#designationSearch',function(){
        $('input[name ="designationSearch"]').keyup(function() {
            filteredList();
            console.log("designChange");
        });
        //$('#itemsList').on('keyup','#refFourSearch',function(){
        $('input[name ="refFourSearch"]').keyup(function() {
            console.log("refFourChange");
            filteredList();
        });
    }
    //------------------------------------------------------------------
    // add or remove item to command
    
    $('#itemsList').on('click','button[name="ean"]',function(){
        //event.preventDefault();
        console.log("id="+$(this).val());
        console.log("commande="+$('#commande').val());
        
        if ($('#commande').val()!=""){
            btnClass=$(this).parent().parent().attr("class");
            console.log("class is "+btnClass);
            ean=$(this).val();
            
            if (btnClass!="chosen"){
                console.log("add item");
                // add item
                $(this).parent().parent().append("<img src='../0101-images/pencil1600.png' class='imgQuantite' myid='"+ean+"'>");
                $(this).parent().parent().addClass('chosen');
                $.ajax({
                    //url : '403-produitEditListjquery.php', // La ressource ciblée
                    url : '0403-addItems.php', // La ressource ciblée
                    type:'POST',
                    data: { ean: ean},
                    success: function(response){                    
                        console.log((response));
                        $(this).parent().first().addClass('chosen');
                    }
                //alert(response);
                });
            }
            else{
                // remove item
                $(this).parents("tr").removeClass('chosen');
                $(this).parent().parent().children(".imgQuantite").remove();
                
                query="delete from prod_commandeList where commande_id="+$('#commande').val()+" and ean="+ean;
                console.log(query);
                $.ajax({
                    url : '../0021-functions/0405-update_data.php', // La ressource ciblée
                    type:'POST',
                    data: { query: query},
                    success: function(response){                    
                        console.log((response));                       
                    }
                //alert(response);
                });
            }
            
        }
        else{
            // Ouvre création d'article pour édition
            //$('#itemsList').on('click','button[name="ean"]',function(){
            ean=$(this).parents("tr").find(".ean").html();
            console.log(ean);
            window.open("./0015-creationDeProduits.php?ean="+ean);
            //});
        }
    });
    //------------------------------------
    
    /*
    $('#myRangeBar').change(function() {
        console.log($(this).val());
        $('[name="myRange"]').val($(this).val());
        $('[name="myForm"]').submit();
    });
    $('[name="myRange"]').change(function() {
        console.log($(this).val());
        $('[name="myRangeBar"]').val($(this).val());
        $('[name="myForm"]').submit();
    });
    */
    
    console.log( "ready!" );
    
    activateSearch();
    filteredList();
    
});
$(".leftNav").find("div").eq(2).addClass('navSelected');
</script>
<script src='0910-jquery_functions.js'></script>
</body>
</html>


