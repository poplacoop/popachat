<?php
@session_start();
$_SESSION['rupture']=0;
$isStock=true;
$isCommande=false;
$titre="Choix des articles";
$_SESSION['listeDesProduits']="listeDesProduits";
//var_dump($_REQUEST);
include "0020-listeGenerique.php";
echo "<div id='itemsList'>";
echo "Un peu de patience...les données se chargent.";
//$str.=createListe($listeProduitsQuery,'ean',$total,$filter,$special,$order);
echo "</div>";

?>
<script>

$(document).ready(function() {
    function activateImg(){
        //alert("activate");
        //--------------------------------------------------------------
        // Quantites
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
        //--------------------------------------------------------------
        // Colis
        // change quantite into input
        $('#itemsList').on('click','.imgColis',function(){
            console.log("colis");
            
            $('#itemsList .chosen').each(function( index ) {
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
        //--------------------------------------------------------------
        // ImgEdit
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
        //------------------------------------------------------------------------------------------
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
        //------------------------------------------------------------------------------------------
        // change colis in database
        $('#itemsList').on('change','input[name="colis[]"]',function(){
            if ($('#commande').val()!=""){
                console.log("nouveau colis ");
                console.log($(this).val());
                console.log($(this).next().val());
                cond=$(this).parents("tr").find(".conditionnement").html();
                colis=$(this).val();
                quantite=Math.floor(colis*cond*1000)/1000;
                //if ($(this).val()!=colis){alert("Attention: la quantité a été modifié car incompatible avec le conditionnement");}
                console.log("conditionnement"+$(this).parents("tr").find(".conditionnement").html());
                //query="update prod_commandeList set quantite="+quantite+" where ean="+$(this).next().val();
                $(this).val(colis);
                $(this).parents("tr").find(".quantite").html(quantite);
                query="update prod_commandeList set quantite="+quantite+" where ean="+$(this).parents("tr").find(".ean").html()+" and commande_id="+$('#commande').val();
                $(this).val(colis);
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
        
        vfamille=$("#famille").val();
        vdepartement=$("#departement").val();
        if (vfamille==""){familleStr="";}else{familleStr=" AND famille='"+vfamille+"'";}
        
        if (vfournisseur==""){fournisseurStr="";}else{fournisseurStr=" AND fournisseur2='"+vfournisseur+"'";}
        if (vdepartement==""){departementStr="";}else{departementStr=" AND departement='"+vdepartement+"'";}
        console.log("tri attribute="+$('#tri').attr("class"));
        if ($('#tri').attr("class")=="icon tri"){mytri=" tri,designation"}else{mytri=" designation"}
        //if ($('#tri').attr("class")=="tridisp clicked"){mytri=" tri,designation"}else{mytri=" designation"}
        console.log(vdepartement.value);
        //alert(fournisseur);
        query="SELECT *,fournisseur as fournisseur2 FROM   prod_articles  where validated<2";
        
        query2="SELECT A.*,B.fournisseur as fournisseur2 FROM ("+query+") as A LEFT JOIN prod_article_fournisseur as B on A.ean=B.ean WHERE NOT ISNULL(B.fournisseur)";
        
        
        
        
        query="("+query+")  UNION ("+query2+")  ";
        query="(SELECT *,fournisseur as fournisseur2 FROM   prod_articles  where validated<2)  UNION (SELECT A.*,B.fournisseur as fournisseur2 FROM (SELECT * FROM   prod_articles  where validated<2) as A LEFT JOIN prod_article_fournisseur as B on A.ean=B.ean WHERE NOT ISNULL(B.fournisseur)) "
        
        
        console.log(query);
        query="SELECT * from ("+query+") as A ";
        
        query+="WHERE ean like '%"+vean+"%' "+departementStr+" AND designation like '%"+vdesign.toUpperCase()+"%' "+familleStr+"  ";
        query+="AND refFour like '%"+vrefFour+"%' "+fournisseurStr+" ORDER BY "+mytri;
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
                        myRange:myRange,
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
    
    
  
    function activateSearch(){
        //$('#itemsList').on('keyup','#eanSearch',function(){
        $('input[name ="eanSearch"]').change(function() {
            console.log("searchClick");
            filteredList();
        }); 
        //$('#itemsList').on('keyup','#designationSearch',function(){
        $('input[name ="designationSearch"]').change(function() {
            filteredList();
            console.log("designChange");
        });
        //$('#itemsList').on('keyup','#refFourSearch',function(){
        $('input[name ="refFourSearch"]').change(function() {
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
                $(this).parent().parent().append("<td class='box'><img src='../0101-images/colis.png' class='imgColis' myid='"+ean+"'></td>");
                $(this).parent().parent().append("<td class='crayon'><img src='../0101-images/pencil1600.png' class='imgQuantite' myid='"+ean+"'></td>");

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
                $(this).parent().parent().children(".crayon").remove();
                $(this).parent().parent().children(".box").remove();
                $(this).parent().parent().children(".colis").html(0);
                $(this).parent().parent().children(".quantite").html(0);
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
    //------------------------------------------------------------------
    // tri libellé
    $('#itemsList').on('click','#tri',function(){
        console.log("tri clicked");
        console.log($('#tri').attr("class"));
        $('#tri').toggleClass("tri");
        filteredList();
        
    });
    
    // display tri column
    $('#itemsList').on('click','#tridisp',function(){
        console.log("tridisp clicked");
        console.log($('#tridisp').attr("class"));
        $('#tridisp').toggleClass("clicked");
        $('#tri').attr("class","icon tri");
        filteredList();
        
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
    // change order of designation
      

});
$(".leftNav").find("div").eq(2).addClass('navSelected');
$(this).attr("href", "documentation/manuel.php#liste");$('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#liste");
});
</script>
<script src='0910-jquery_functions.js'></script>
</body>
</html>

