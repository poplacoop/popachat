<?php
@session_start();
$isStock=true;
$isCommande=false;
$titre="Gestion des Stocks";
$_SESSION['listeDesProduits']='stock';
//var_dump($_REQUEST);
if ($_SESSION['userInfo']['admin']){
//echo "<br>start of page: Current time is " . date("h:i:s:A");
}
//var_dump($_SESSION);
include "0020-listeGenerique.php";
$stockTable="";
$totalStockStr="";
//----------------------------------------------------------------------
// display stock level by famille
//----------------------------------------------------------------------
if (isset($_REQUEST['stockLevel'])){
    $stockQuery="SELECT last.ean,last.thedate,stock from (SELECT ean,max(thedate) as thedate,max(id) as id 
            FROM `prod_stock` where not isnull(stock) group by ean) as last 
            left outer join prod_stock as stock on last.ean=stock.ean and last.thedate=stock.thedate and last.id=stock.id
            ORDER BY `last`.`thedate` ASC";
    $query="SELECT * from prod_articles WHERE validated<2";
    $query="select article.ean,designation,prixAchat,prixAchat*stock as valPrixAchat,prixVente,prixVente*stock as valPrixVente,stock as stockQuantite,
        uniteVente,departement,famille,fournisseur from ($query) as article 
        left outer join ($stockQuery) as stocktbl on article.ean=stocktbl.ean";
    
    switch($_REQUEST['stockLevel']){
        case "departement":
            $query="SELECT A.departement,B.titre,sum(valPrixAchat) as valAchat,sum(valPrixVente) as valVente from ($query) as A 
            left outer join prod_departement as B 
            on A.departement=B.id group by departement";
            break;
        
        case "fournisseur":
            $query="SELECT A.fournisseur,B.titre,sum(valPrixAchat) as valAchat,sum(valPrixVente) as valVente from ($query) as A 
            left outer join prod_fournisseur as B 
            on A.fournisseur=B.id group by fournisseur";
            
            break;
        
        case "famille";
            $query="SELECT A.famille,B.titre,sum(valPrixAchat) as valAchat,sum(valPrixVente) as valVente from ($query) as A 
            left outer join prod_famille as B 
            on A.famille=B.id group by famille";
            
            break;

    }
    //echo $query;
    $table=query_table($query);
    // compute stock total
    $totalQuery="SELECT sum(valAchat) as stockAchat,sum(valVente) as stockVente from ($query) as A;";
    $totalTbl=query_table($totalQuery);
    

    //----------------------------------------------------
    // create Excel and html
    include "../0021-functions/0506-exportToXls.php";
    $fileName=date("Y-m-d")."_".$_REQUEST['stockLevel']."_bilanStock.xls";
    $stockTable.="<a href='./files/$fileName'>$fileName</a><br>";
    $stockTable.=exportToXls($fileName,$table,[]);
    
    $totalStockStr.="<p>Stock ".number_format($totalTbl[1]['stockAchat'],0)." euros en prix d'achat</p>";
    //echo $stockStr;
    
}
$stockStr= "<form name='stockForm' id='stockForm'>
    <div id='ventilationStock'>Répartition des stocks<br>";

$stockStr.=$totalStockStr;
$stockStr.="<button type='submit' name='stockLevel' value='departement' >departement</button>
        <input type='submit' name='stockLevel' value='famille' onclick='submit()'></input>
        <input type='submit' name='stockLevel' value='fournisseur' onclick='submit()'></input>
    </div>
    </form>";
$stockStr.=$stockTable;
    echo $stockStr;

//----------------------------------------------------------------------
//   Rupture de stock
//
if ($_SESSION['userInfo']['admin']){
    echo "updateStockInPlu";
    $nbOfLineToTreat=500;
    updateStockPlu($nbOfLineToTreat);
    echo "updateStockInPlu done";
    
    $str="\n<form>";
    // graphiques
    if (isset($_REQUEST['rupture'])){
        $_SESSION['rupture']=1;
        $checked='checked';
    }
    else{
        $_SESSION['rupture']=0;
    }
    $str.= "<div class='theRange'>
             
             <div> rupture de stock?</div>";
            $str.="
            <div class='form-check form-switch'>
              <input class='form-check-input' type='checkbox' id='flexSwitchCheckDefault' name='rupture' $checked onchange='submit();'/>
            </div>
        </div>";
    $str.="</form>";

echo $str;
$str="";
//----------------------------------------------------------------------

$queryRupture="SELECT ar.* FROM `prod_rupture_list` as ru LEFT OUTER JOIN prod_articles as ar on ar.ean=ru.ean"; // list articles in list.
//----------------------------------------------------------------------
// Longueur des stocks
//include "longueurdesstocks.php";
include "0604-rupture.php";
}
echo $str;

if (!isset($_REQUEST['stockLevel'])){
    echo "<div id='itemsList'>";
    //$str.=createListe($listeProduitsQuery,'ean',$total,$filter,$special,$order);
    echo "</div>";
}
?>
<script>

$(document).ready(function() {
 /*   $("form[name='myForm']").submit(function(e){
        e.preventDefault();
    });*/

    function activateImg(){
        //alert("activate");
        // change quantite into input
        /*$('#itemsList').on('click','.imgQuantite',function(){
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
        });
        // change quantite in database
        $('#itemsList').on('change','input[name="quantite[]"]',function(){
            if ($('#commande').val()!=""){
                console.log("quantite nouvelle ");
                console.log($(this).val());
                console.log($(this).next().val());
                
                query="update prod_commandeList set quantite="+$(this).val()+" where ean="+$(this).next().val();
                console.log(query);
                $.ajax({
                    url : '../functions/0405-update_data.php', // La ressource ciblée
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
        
        
        $('#itemsList').on('click','.imgErase',function(){      
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
            url : '../functions/0405-update_data.php', // La ressource ciblée
            type:'POST',
            //async: false,
            data: { query: query},
            success: function(response){ 
                                
                //$(this).html(response);
                console.log(response);
            } 
        });*/

    };
    

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
        //console.log(vdepartement.value);
        //alert(fournisseur);
        query="SELECT * FROM (SELECT * FROM prod_articles ) as prod_articles WHERE ean like '%"+vean+"%' "+departementStr+" AND designation like '%"+vdesign.toUpperCase()+"%' "+familleStr+"  AND refFour like '%"+vrefFour+"%' "+fournisseurStr+" ORDER BY validated,designation;";
        //alert(query);
        console.log(query);
        myRange=$("input[name='myRange']").val();
        graphSales=<?php echo "'".$order['graphSales']."'"; ?>;
        //$("input[name='graphSales']").val();
        console.log("graphSales="+graphSales);
        // modified: POST
        $.ajax({
                //url : '403-produitEditListjquery.php', // La ressource ciblée
                url : '0400-createListeforListeDesProduits.php', // La ressource ciblée
                type:'POST',
                data: { query: query,
                        graphSales:graphSales,
                        graphStock:'on',
                        myRange:myRange
                        
                     },
                success: function(response){  
                    //console.log("response="+response);                 
                    $("#itemsList").html(response);
                    console.log("length is "+$("#itemsList").find("th").eq(3).html());
                    $("#itemsList").find("th").eq(3).html("<button type='button' name='buttonStock'>Stock</button>");
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
    
    // gestion des stocks
    // create input box when button stock is cliked
    $('#itemsList').on('click','button[name="buttonStock"]',function(){
        // iterer sur les stocks pour mettre input
        $('#itemsList .stock').each(function( index ) {
            if($(this).find("input").length==0){
                val=$(this).html(); // quantite
                $(this).html("<input name='editStock' value='"+val+"'></input>"); 
            }
        });
    });
    //------------------------------------------------------------------
    // update stock
    $('#itemsList').on('change','input[name="editStock"]',function(event){
        // iterer sur les stocks pour mettre input
        newStock=$(this).val(); // stock
        ean=$(this).parents("tr").children(".ean").html();
        console.log("editStock"+val);
        console.log($(this).parents("tr").children(".ean").html());   
                 
        
        console.log(query);
        $.ajax({
            url : '0404-modifyStock.php', // La ressource ciblée
            type:'POST',
            data: { ean:ean,stock:newStock},
            success: function(response){                    
                console.log((response));                       
            }
        //alert(response);
        });
    });
    //------------------------------------------------------------------
    // graph Stock
    //
    // compute stock starting from myrange weeks before current data for ean
    $('#itemsList').on('click','.imgStock',function(){
        //$('img.imgEdit').click(function() {
        console.log("clicked graphStock");
        //id=$(this).prop('class').split(" ")[1];
        theParent=$(this).parents("tr");
        theParentHtml=theParent.html();
        theParentIndex=$("#itemsList").find("table").find("tr").index(theParent);
        console.log("theparentindex="+theParentIndex);
        console.log("theparentHtml=",theParentHtml);
        ean=$(this).parents("tr").children(".ean").html();
        console.log("ean="+ean);
        myRange=$("input[name='myRange']").val();
        $.ajax({
            url : '0405-createGraphStock.php', // La ressource ciblée
            type:'POST',
            data: { ean: ean,
                    myRange:myRange
            },
            success: function(response){  
                console.log("response="+response);                 
                //$("#itemsList").find("table").find("tr").eq(theParentIndex).html(response);
                $("#theGraph").html(response);
                //$("#itemsList").find("table").find("tr").eq(theParentIndex).html(theParentHtml+"\n");
                
            } 
                    
                //alert(response);
        });        
    });
    //------------------------------------------------------------------
    //
    //
    
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
        //submit();
        return false;
    });
    

    //filteredList();
    $("#searchList").first().removeClass("theList");
    //alert($("#searchList").first().attr("class"));

    $("#searchList").first().addClass("editList");
    
    

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
    // add or remove item to stock
    //------------------------------------------------------------------
    $('#itemsList').on('click','button[name="ean"]',function(){
        //event.preventDefault();
        console.log("id="+$(this).val());
        console.log("commande="+$('#commande').val());
        // Case of Rupture Active
        if ($('input[name="rupture"]').val()!=""){
            console.log("rupture activé clik");
            btnClass=$(this).parent().attr("class");
            console.log("class is "+btnClass);
            ean=$(this).val();
            console.log("rupture activé clik"+ean);
            console.log(btnClass);
            if (btnClass!="designation rupture"){
                console.log("add item");
                // add item
                $(this).parent().addClass('rupture');
                query="insert into prod_rupture_list (ean,selected,author,date) VALUES ("+ean+",1,"+<?php echo "\"'".$_SESSION['userInfo']['userId']."','".date("Y-m-d")."'\"";?>+")" ;
                console.log(query)
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
            else{
                // remove item
                $(this).parent().removeClass('rupture');
                query="delete from prod_rupture_list where ean='"+ean+"'";
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
    
    //------------------------------------------------------------------
    // modify validation
    //------------------------------------------------------------------
    $('#itemsList').on('click','img.minus',function(){
        //event.preventDefault();
        console.log("minus clicked");
        console.log("val="+$(this).parent().children().eq(1).html());
        validated=$(this).parent().children().eq(1).text();
        ean=$(this).parents("tr").first().children().first().html();
        if (validated==0){validated=0;}else{validated-=1;}
        $(this).parent().children().eq(1).val(validated);
        $(this).parent().children().eq(1).html(validated);
        query="update prod_articles set validated="+validated+" where ean='"+ean+"'";
        console.log(query)
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
            type:'POST',
            data: { query: query},
            success: function(response){                    
                console.log((response));                       
            }
            
                //alert(response);
        });
    });
    $('#itemsList').on('click','img.plus',function(){
        //event.preventDefault();
        console.log("plus clicked");
        console.log($(this).parent().children().eq(1).val());
        validated=parseInt($(this).parent().children().eq(1).text());
        ean=$(this).parents("tr").first().children().first().html();
        if (validated==5){validated=5;}else{validated=validated+1;}
        $(this).parent().children().eq(1).val(validated);
        $(this).parent().children().eq(1).html(validated);
        query="update prod_articles set validated="+validated+" where ean='"+ean+"'";
        console.log(query)
        $.ajax({
            url : '../0021-functions/0405-update_data.php', // La ressource ciblée
            type:'POST',
            data: { query: query},
            success: function(response){                    
                console.log((response));                       
            }
            
                //alert(response);
        });
    });    
    
    
    //-----------------------------------------------------------------
    // range bar

    
    
    console.log( "ready!" );
    
    activateSearch();
    filteredList();
    //console.log("look for th="+$('#theRange').html()+"/");
    
    
    
});


/*function exportXls(){
        vean=$("#eanSearch").val();
        vdesign=$("#designationSearch").val();
        vrefFour=$("#refFourSearch").val();
        vfournisseur=$("#fournisseur").val();
        vfamille=$("#famille").val();
        vdepartement=$("#departement").val();
        if (vfamille==""){familleStr="";}else{familleStr=" AND famille='"+vfamille+"'";}
        if (vfournisseur==""){fournisseurStr="";}else{fournisseurStr=" AND fournisseur='"+vfournisseur+"'";}
        if (vdepartement==""){departementStr="";}else{departementStr=" AND departement='"+vdepartement+"'";}
        //console.log(vdepartement.value);
        //alert(fournisseur);
        query="SELECT * FROM (SELECT * FROM prod_articles where validated<2) as prod_articles WHERE ean like '%"+vean+"%' "+departementStr+" AND designation like '%"+vdesign.toUpperCase()+"%' "+familleStr+"  AND refFour like '%"+vrefFour+"%' "+fournisseurStr+" ORDER BY designation;";
        query.replace(/'/g, "\\'");
        console.log("0017-stockExcel.php?query=\'"+query+"\'");
        $("#exportXls").attr("href","0017-stockExcel.php?query='"+query+"'");
        
    }*/
$(".leftNav").find("div").eq(6).addClass('navSelected');

$('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#stock");
});


</script>
<script src='0910-jquery_functions.js'></script>
</body>
</html>

