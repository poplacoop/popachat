<?php
@session_start();
include "0000-initFilesProd.php";
include "0500-listFunctions.php";
include "0501-graphFunctions.php";
include "0504-listFournisseur.php";

if (isset($_REQUEST['retour'])){
    header("Location:0011-fournisseur.php");
}

echo myheader();
echo "<body>
    <div class='topBanner'>";
echo menu($menuFilter);
$htmlNouveau="<form method='post' enctype='multipart/form-data'>";
$htmlNouveau.= "<div><button name='retour'>Retour</button></div>";

//var_dump($_SESSION);
//----------------------------------------------------------------------
// Treat Request
 
$title=['id'=>['','no'],
'titre'=>['Nom du fournisseur','nom'],
'adresse1'=>['','adresse1'],
'adresse2'=>['','adresse2'],
'adresse3'=>['','adresse3'],
'adresse4'=>['','adresse4'],
'telephone'=>['Téléphone du fournisseur','téléphone'],
'contact'=>['Contact du fournisseur','nom du contact'],
'email'=>['E-mail du contact','E-mail du contact'],
'referentPop'=>['referentPop','Nom du référent'],
'referentPop2'=>['referentPop2','Nom du référent'],
'comments'=>['','remarques'],
'formatXLS'=>['formatXLS','format d\'importation des fichiers (admin)'],
'typeImport'=>['typeImport','type de l\'import (admin)'],
'francoMontant'=>['en euro','franco en montant'],
'francoColis'=>['kg ou unité','franco en nombre de colis',''],
'frequenceCommandes'=>['semaines','Fréquence des commandes',''],
'discountThreshold'=>['en Euros','seuil pour la réduction',''],
'discount'=>['example: 0.14 pour 14%','reduction en fraction',''],
'delaiDeLivraison'=>['en jours (saisir un nombre)','Délai de livraison'],
'fichierImport'=>['Nom du fichier du founisseur','Fichier de Commande'],
'colisSheetName'=>['','Nom de la feuille à compléter']
];
$filter=array_keys($title);
//dispArray($filter);
$request=[];
if (isset($_REQUEST)){
    foreach ($filter as $keyword){
        $request[$keyword]="";
        if (isset($_REQUEST[$keyword])){$request[$keyword]=$_REQUEST[$keyword];}
    }
}

//----------------------------------------------------------------------
// prepare lists
$itemList=['id'=>['id','titre','SELECT id,titre from prod_fournisseur'],
            'referentPop'=>['id','NomPrenom','SELECT id,concat(prenom," ",nom) as NomPrenom from prod_user']];
$itemList=['referentPop'=>['id','NomPrenom','SELECT id,concat(prenom," ",nom) as NomPrenom from prod_user'],
           'referentPop2'=>['id','NomPrenom','SELECT id,concat(prenom," ",nom) as NomPrenom from prod_user']];
$list=[];
foreach ($itemList as $item=>$tab){
    $key=$tab[0];
    $val=$tab[1];
    $query=$tab[2];
    $table=query_table($query);
    $dico[$item]=create_one_field_dictionnary($table,$key,$val);
    $list[$item]= listeSelect($dico,$item,$item,$request[$item],1); // the 1 if for order
}
//var_dump($dico['referentPop']);
//-----------------------------------------------------------------------
// prepare list of users for option select selKey
$nb=1;
$comma="";
$arr="var user=[";
foreach ($dico['referentPop'] as $key=>$row){
    $arr.=$comma.$key;
    $comma=",";
}
$arr.="];";
//echo $arr;
//-----------------------------------------------------------------------

// Edit fournisseur
// create list of attributes

if ($order['action']=="new"){
    array_shift($request);
    //var_dump($request);
    $record=true;
    $compulsoryKeyword=[];
    foreach ($title as $key=>$row){
            $compulsoryKeyword[$key]=$row[0];
    }
    
    //dispArray($compulsoryKeyword);

    unset($compulsoryKeyword['id']);
    unset($compulsoryKeyword['adresse3']);
    unset($compulsoryKeyword['adresse4']);
    unset($compulsoryKeyword['contact']);
    unset($compulsoryKeyword['referentPop2']);
    unset($compulsoryKeyword['comments']);
    unset($compulsoryKeyword['formatXLS']);
    unset($compulsoryKeyword['typeImport']);
    unset($compulsoryKeyword['delaiDeLivraison']);
    unset($compulsoryKeyword['discount']);
    unset($compulsoryKeyword['discountThreshold']);
    unset($compulsoryKeyword['frequenceCommandes']);
    unset($compulsoryKeyword['fichierImport']);
    unset($compulsoryKeyword['colisSheetName']);
    $compulsory=array_keys($title);
    array_shift($compulsory);
    echo "<br>";
    //dispArray($compulsoryKeyword);
    //echo $order['fournisseur'];
    foreach ($compulsoryKeyword as $key=>$name){
        if ($request[$key]==""){
            $record=false;
            echo "Il manque $key.<br>";
        }
    }

    if ($record){
        $queryFournisseur="SELECT max(id) as id from prod_fournisseur";
        $tableFournisseur=query_table($queryFournisseur);
        $maxId=$tableFournisseur[1]['id']+1;
        $request['id']=$maxId;
        //var_dump($request);
        if($request['formatXLS']==""){$request['formatXLS']=0;}
        if($request['delaiDeLivraison']==""){$request['delaiDeLivraison']=0;}
        $attributeName="(";
        $attributeValue="(";
        $comma="";
        $listRequest=$filter;
        
        //array_shift($listRequest); // get rid of "id"
        foreach ($listRequest as $name){
            //echo $name."-";
            if (isset($request[$name])){
                if ($request[$name]!=""){
                    //echo $name."<br>";
                    $attributeName.=$comma.$name."";
                    $attributeValue.=$comma."'".$request[$name]."'";
                    $comma=",";
                }
            }
        }
        $attributeName.=",author)";
        $attributeValue.=",'".$_SESSION['userInfo']['userId']."')";

        $query="INSERT INTO `prod_fournisseur` ".$attributeName." VALUES ".$attributeValue;
        
        echo "<br>".$query;
        simple_query($query);
        
        header("Location: ./0019-addFournisseur.php?action=modify&fournisseur=".$request['id']);
    }
    else{
        echo "Pas encore assez de données pour pouvoir enregistrer le fournisseur";
    }
}
//var_dump($_REQUEST);
//dispArray($filter);
// only change if supplier is set.
if ((isset($_REQUEST['change'])||(isset($_REQUEST['override']))) && ($request['id']!="")){
    //var_dump($_REQUEST);
    //echo "wants to change";
    $id=$request['id'];
    //echo "ean=".$ean;
    $query="select * from prod_fournisseur where id =$id";
    $dicoOneArticle=query_table_dico($query);
    $dicoOneArticle=$dicoOneArticle[0];
    //dispArray($dicoOneArticle);
    //dispArray($filter);
    //dispArray($request);
    if (!isset($_REQUEST['override'])){
            echo "<div class='redBackground'>L'autorisation de modification n'a pas été sélectionnée.</div>";
    }
    else{
        foreach ($filter as $keyword=>$val){
            //echo $keyword."-".$val."<br>";
            //echo $request[$val]." and ".$dicoOneArticle[$val]."<br>";

            //if (($request[$val]!="") and ($request[$val]!==$dicoOneArticle[$val])){
            if (($request[$val]!==$dicoOneArticle[$val])){
                if (($val=="referentPop2")&&($request[$val]=="")){
                    $query="UPDATE prod_fournisseur SET $val=NULL where id=$id";
                }
                else{
                    $query="UPDATE prod_fournisseur SET $val='".addslashes($request[$val])."' where id=$id";
                }
                //echo "<br>".$query."</br>";
                simple_query($query);
                echo "<div class='modified'>Mise à jour du fournisseur no $id</div>";
            }
        }
        //------------------------------------------------------------------
        // get File and save it
        //------------------------------------------------------------------
        if (isset($_FILES["importFile"]["name"])){
            $target_dir = "./files/";
            $target_file = $target_dir . basename($_FILES["importFile"]["name"]);

            move_uploaded_file($_FILES["importFile"]["tmp_name"], $target_file);
            $filename=$_FILES["importFile"]["name"];
            echo $filename."<br><br>";
            // load into database
            $userId=$_SESSION['userInfo']['userId'];
            $query="UPDATE `prod_fournisseur` SET fichierImport='".$filename."' WHERE id=".$_SESSION['fournisseur'].";";
            simple_query($query);
        }
    }
    
}

//----------------------------------------------------------------------
// Creation du formulaire
//----------------------------------------------------------------------
echo "<br>";
//var_dump($filter);
if ($order['action']=='new'){array_shift($filter);}

/*
if ($order['action']=='modify'){
    if($order['titre']!=""){
        $request['titre']
    }
    
}
*/


echo "<br><br>";

//var_dump($filter);
$htmlNouveau.= "<div class='ajout'>";

if ($order['action']=="new"){
    $htmlNouveau.= "Créer un fournisseur\n";
}
else{
    $htmlNouveau.= "Modifier un fournisseur\n";
}
$htmlNouveau.="<table id='creationTable'>";
//dispArray($list);
foreach ($filter as $attr){
    $htmlNouveau.="</tr><td>".$title[$attr][1]."</td>";
    if (array_key_exists($attr,$itemList)){
        //echo "recognised $attr";
        $htmlNouveau.="<td>".$list[$attr]."</td>";
    }
    else{
        $htmlNouveau.="<td><input id='$attr' name='$attr' value='".addslashes($request[$attr])."'></input></td><td>".$title[$attr][0]."</td>";
    }
    
    
    $htmlNouveau.="</tr>";
}
$htmlNouveau.="<tr><td>Saisir le fichier</td>
    <td><input type='file' name='importFile' id='importFile'></td>
    <td>Fichier excel du fournisseur.</td></tr>";
$htmlNouveau.="</table>";
echo $htmlNouveau;
//var_dump($_REQUEST);
//var_dump($order);
if ($order['action']=="modify"){
    echo "<div class='theRange'><button name='change' >Modifier</button>\n";
    
    echo '
    Autorisation de modification:<div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" name="override" ">
            </div>';
    echo "</div>";
    
    //echo "Autoriser la modification<input id='override' name='override' type='checkbox' ></input>\n";
}
//echo "action".$order['action'];
if ($order['action']=="new"){echo "<button name='action' value='new'>Ajouter le fournisseur</button>";
    echo "<input type='hidden' name='action' value='new'></input>";
}
else{
    echo "<input type='hidden' name='action' value='modify'></input>";
}
//----------------------------------------------------------------------
// display format importation
if ($order['fournisseur']!=""){
    $sql="SELECT formatXLS,fichierimport from prod_fournisseur WHERE id='".$order['fournisseur']."';";
    $table=query_table($sql);
    $format=$table[1]['formatXLS'];
    $sql="SELECT * from prod_import_readxls WHERE format='".$format."';";
    echo "format=".$format;
    $table=query_table($sql,0);
    displayinhtml($table,"",0);
}
?>
<script>
function fill_form(){
    console.log("on a changé de fournisseur");
            $("#ean").parents("tr").children().eq(2).html("code ean13 valide");
            $("#ean").parents("tr").children().eq(2).addClass("inputGreen");
            $("#ean").parents("tr").children().eq(2).removeClass("inputRed");
            // look for article
            query="SELECT * FROM prod_fournisseur WHERE id="+$("#id").val();
            console.log(query);
            $.ajax({
                url : '../0021-functions/0406-retrieve_data.php', // La ressource ciblée
                type:'POST',
                data: { query: query},
                success: function(response){ 
                    
                    var data = JSON.parse(response);
                    
                    //$("#tableContain").show();
                    $("#titre").val(data.titre);
                    $("#adresse1").val(data.adresse1);
                    $("#adresse2").val(data.adresse2);
                    $("#adresse3").val(data.adresse3);
                    $("#adresse4").val(data.adresse4);
                    $("#telephone").val(data.telephone);
                    
                    <?php echo $arr;?>
                    $("#contact").val(data.contact);
                    $("#email").val(data.email);
                    //console.log ("refPop="+data.referentPop+"inarray="+$.inArray(6,user))
                    //console.log("referentPop"+$("#referentPop").children("option").eq(data.referentPop).html()+"no"+data.referentPop);
                    //console.log ("refPop="+data.referentPop+"inarray="+$.inArray(Number(data.referentPop),user))
                    
                    
                    
                    referentPopIdx=$.inArray(Number(data.referentPop),user)
                    $("#referentPop").children("option").eq(referentPopIdx+1).attr("selected","selected");
                    referentPop2Idx=$.inArray(Number(data.referentPop2),user)
                    $("#referentPop2").children("option").eq(referentPop2Idx+1).attr("selected","selected");
                    
                    $("#frequenceCommandes").val(data.frequenceCommandes);
                    $("#comments").val(data.comments);
                    $("#formatXLS").val(data.formatXLS);
                    $("#typeImport").val(data.typeImport);
                    $("#francoMontant").val(data.francoMontant);
                    $("#francoColis").val(data.francoColis);
                    $("#delaiDeLivraison").val(data.delaiDeLivraison);
                    $("#discount").val(data.discount);
                    $("#discountThreshold").val(data.discountThreshold);
                    $("#fichierImport").val(data.fichierImport);
                                    
                    //$(this).html(response);
                    console.log(response);
                } 
            });
}
    
    
$(document).ready(function(){

    function displayMessage(e){
        console.log("display message");
        if(e.keyCode==13){
            event.preventDefault();
            fill_form();  
        }
        
    };
    $("#creationTable").on("keydown","#id",function(e){displayMessage(e)});
    
    $(document).on('submit', '.creationTable', function(event){
        event.preventDefault();
    });
    console.log ("readty");
    
});
$('#help').click(function(){
    console.log( 'help clicked ' );
    $(this).attr("href", "documentation/manuel.php#ajoutFournisseur");
});


   $(".leftNav").find("div").eq(7).addClass('navSelected');         
</script>
<?php
if ($order['action']="modify"){
    echo "<script>
    $(document).ready(function(){
        $('#id').val(".$order['fournisseur'].");
        fill_form();
    });
    </script>";
}
?>

</body>
</html>

