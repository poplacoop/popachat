<?php
@session_start();

include "../../0000-head.php";
include "../../0021-functions/0505-miscellaneousFunctions.php";
include "../../0021-functions/0501-retrieveFunctions.php";
$addPath="../";
echo myheader("<link rel='stylesheet' href='style.css?v1.2'>",$addPath);
//----------------------------------------------------------------------
// Record message
if (isset($_REQUEST['msg'])){
    if ($_REQUEST['msg']!=""){
        $msg=$_REQUEST['msg'];
        $date=$_REQUEST['datetime'];
        $author=$_SESSION['userInfo']['userId'];
        $query="INSERT INTO forum (datetime,author,message) VALUES('$date',$author,'".htmlentities(addslashes($msg))."')";
        simple_query($query);
        //header("Location:blog.php");
    }
}
if (isset($_REQUEST['answers'])){
    foreach ($_REQUEST['answers'] as $key=>$row){
            if ($row!=""){
                //echo $key."=".$row;
                $query="UPDATE forum SET answer='".htmlentities(addslashes($row))."',author=".$_SESSION['userInfo']['userId']." where id=$key";
                //echo $query;
                simple_query($query);
            }
        
    }
    
}
//----------------------------------------------------------------------
// prepare lists

$itemList=['user'=>['id','NomPrenom','SELECT id,concat(prenom," ",nom) as NomPrenom from prod_user']];
$list=[];
foreach ($itemList as $item=>$tab){
    $key=$tab[0];
    $val=$tab[1];
    $query=$tab[2];
    $table=query_table($query);
    $dico[$item]=create_one_field_dictionnary($table,$key,$val);

}
$query="SELECT * from forum";
$tableMsg=query_table_dico($query);
//echo $query;

//$_SESSION['userInfo']['admin']=1;
//echo date("Y-m-d H:i:s");
?>

<body class='doc'>

	<h1>Le blog des utilisateurs</h1>
    <button id="retour" value="retour">RETOUR</button>
    <p class='pageTitle'><a href="">Merci de consigner les difficultés rencontrées</a></p>
    <?php
    echo "<form id='myForm'>";
    echo "<table class='blog'>";
    echo "<tr><th>Auteur</th><th>Date</th><th>Difficulté</th><th>Réponse</th></tr>";
    echo "<tr>
            <td><input name='user' value='".$_SESSION['userInfo']['userId']."' type=hidden></input>".$dico['user'][$_SESSION['userInfo']['userId']]."</td>
            <td><input type='date' name='datetime' value='".date("Y-m-d")."'/></td>
            <td><textarea name='msg'></textarea ></td><td><button name='go' type='submit'>Enregistrer</button></td>";

    
    echo "</tr>";
    foreach ($tableMsg as $row){
        echo "<tr>
            <td>".$dico['user'][$row['author']]."</td>
            <td>".$row['datetime']."</td>
            <td>".$row['message']."</td>";
            
        if ($_SESSION['userInfo']['admin']){
            echo "row".$row['answer'];
            echo "<td><textarea name='answers[".$row['id']."]' rows=1 col=20 >".$row['answer']."</textarea><button >GO</button></td>";
            echo "<td class='erase'><img src='../../0101-images/redCross.png' class='imgIcon' onclick='erase(".$row['id'].")')></img></td>";
            echo "<td class='valid'><img src='../../0101-images/greenChecked.png' class='imgIcon' onclick='validate(".$row['id'].")')></img></td>";
        
            
        }
        else{
            echo "<td>".$row['answer']."</td>";
        }
        echo "</tr>";
        
    }
    echo "</table>";
    
    
    
    
    echo "<table class='blog'>";
    
    
    
    
    echo "</table>";
    echo "<button name='go' type='submit'>Enregistrer</button>";
    echo "</form>";
    ?>

<script>
$(document).ready(function() {
    $('#retour').click(function(){
        window.close();
    });
    
    
});
function erase(id){
        query='DELETE FROM forum WHERE id='+id;
        if (confirm("Etes vous-sûr d'effacer "+id+"?")){
            $.ajax({
                url : '../../0021-functions/0405-update_data.php', // La ressource ciblée
                        type:'POST',
                        data: { query: query},
                        success: function(response){ 
                                            
                            $(this).html(response);
                            console.log(response);
                        } 
                });
        }
        myForm.submit();
    }


</script>

</body>
</html>



</html>
