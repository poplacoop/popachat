<?php
@session_start();

require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0501-retrieveFunctions.php";
include "../0021-functions/0505-miscellaneousFunctions.php";
//----------------------------------------------------------------------
// treat data
$incomplet="";
if (isset($_REQUEST['an'])){
    $request=$_REQUEST['an'];
    if (sizeof($request)==4){
        //dispArray($request[3]);
        if ($request[3]!=[0,0,0,0]){// check that all answers have been given
            $questList=[1,2];
            //var_dump($request);
            foreach ($questList as $key=>$line){
                //var_dump($line);
                //echo "<br>";
                $survey=1;
                $questNb=($key+1);
                $ans=$request[$questNb-1];
                $query="INSERT INTO sondage_user_survey (survey,question,reponse,IP) VALUES($survey,$questNb,$ans,'".$_SERVER['REMOTE_ADDR']."');";
                //echo $query;
                simple_query($query);

                //echo "<br>";
            }

            //var_dump($_REQUEST['comment']);
            foreach ($_REQUEST['comment'] as $key=>$comment){
                if ($comment!=""){
                    $query="INSERT INTO sondage_commentaires (survey,question,comments,IP) VALUES($survey,".($key+1).",'$comment','".$_SERVER['REMOTE_ADDR']."');";
                    echo $query;
                    simple_query($query);
                }
            }



            //var_dump($request[2]);
            //echo "<br>";
            $survey=1;
            $questNb=3;
            $ans=$request[$questNb-1];
            foreach ($request[$questNb-1] as $ans){
                $query="INSERT INTO sondage_user_survey (survey,question,reponse,IP) VALUES($survey,$questNb,$ans,'".$_SERVER['REMOTE_ADDR'] ."');";
                //echo $query;
                simple_query($query);
                //echo "<br>";
                
            }
            //var_dump($request[3]);
            //echo "<br>";
            $survey=1;
            $questNb=4;
            $ans=$request[$questNb-1];
            foreach ($request[$questNb-1] as $ans){
                if ($ans!="0"){
                    $query="INSERT INTO sondage_user_survey (survey,question,reponse,IP) VALUES($survey,$questNb,$ans,'".$_SERVER['REMOTE_ADDR']."');";
                    //echo $query;
                    simple_query($query);
                }
                
                //echo "<br>";
            }
            
        }
    }
    else{
        $incomplet="<div class='incomplet' >Répondez à toutes les questions s'il vous plaît.</div>";
        
        
    }
}


//----------------------------------
// display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//----------------------------------
include "../0000-head.php";
include "../0002-login.php";
echo myheader("<link rel='stylesheet' href='0100-sondageStyle.css?v1.2'>");
echo "<body>";
$userRights=$_SESSION['userInfo']['userRights'];
// modify header
echo "<script>
$(function () {
            $(document).attr('title', 'pop Achat');
        });
</script>";
//----------------------------------------------------------------------
// check values
$menuList=['m'=>'import'];  
$menuFilter="m";

include '0001-menuSondage.php';
echo "<body>
<h1>Sondage POP sur les produits hygiène cosmétique</h1>
    <div class='topBanner'>";
    
echo "    <img src='images/logo_pop_la_coop_0.jpg'>";
    
echo $incomplet;
    
    
//echo menuProd($menuFilter);
echo "</div>";

// get data
$query="SELECT * FROM sondage_questions  where survey=1 order by survey,id";
$questionTbl=query_table($query,1);
//displayinhtml($questionTbl);
$query="SELECT * FROM sondage_answers where survey=1  order by survey,question";
$answersTbl=query_table($query,1);
//displayinhtml($answersTbl);
array_shift($answersTbl);
//
$answers=[];
$question="";
$list="";
foreach($answersTbl as $row){
    //dispArray($row);
    if ($question!=$row['question']){
        $question=$row['question'];
        if ($question!=""){
            array_push($answers,$list);
            $list=[];
        }
    }
    array_push($list,[$row['id'],$row['question'],$row['reponse']]);        
}
array_push($answers,$list);

//echo "answers0<br>";
//var_dump($answers[1]);



$query="SELECT * FROM sondage_user_survey where survey=1";
$matchTbl=query_table($query);



echo "<form>";
echo "<div id='sondage'>";
//
// question 1
$quesNb=1;
$check=['',''];
if (isset($request[$quesNb-1])){
    $check[$request[$quesNb-1]-1]="checked";
}
echo "<div class='question'>";
$ans=$answers[$quesNb];
//var_dump($ans);
echo "1) ".$questionTbl[1]['libelle'];
echo "</div>";
echo "<div class='answer'>";
echo "<div><input type=radio name=an[0] value=1 ".$check[1-1].">".$ans[0][2]."</input></div>";
echo "<div><input type=radio name=an[0] value=2 ".$check[2-1].">".$ans[1][2]."</input></div>";
echo "<div>Pourquoi?</div><div><textarea class='ques1' rows=2 column=140 name='comment[0]'></textarea></div>";
echo "</div>";

// question 2
$quesNb=2;
$check=['','',''];
if (isset($request[$quesNb-1])){
    $check[$request[$quesNb-1]-1]="checked";
}
echo "<div class='question'>";
$ans=$answers[$quesNb];
//var_dump($ans);
echo "2) ".$questionTbl[2]['libelle'];
echo "</div>";
echo "<div class='answer'>";
echo "<div><input type=radio value=1 name=an[1] ".$check[1-1].">".$ans[0][2]."</input></div>";
echo "<div><input type=radio value=2 name=an[1] ".$check[2-1].">".$ans[1][2]."</input></div>";
echo "<div><input type=radio value=3 name=an[1] ".$check[3-1].">".$ans[2][2]."</input></div>";

echo "</div>";

// question 3
$quesNb=3;
$check=['','','','','','','','','','','','','','','','',''];

if (isset($request[$quesNb-1])){
    //var_dump($request[$quesNb-1]);
    foreach ($request[$quesNb-1] as $nb){
        $check[$nb]="checked";
    }
}
echo "<div class='question'>";
$ans=$answers[$quesNb];
//var_dump($ans);
echo "3) ".$questionTbl[3]['libelle'];
echo "</div>";
echo "<div class='answerCol'>";

for ($i=0;$i<sizeof($ans);$i++){
    echo "<div><input type=checkbox name='an[2][]' value=".$i." ".$check[$i].">".$ans[$i][2]."</input></div>";
}
echo "</div>";


// question 4
echo "<div class='question'>";
$ans=$answers[4];
//var_dump($ans);
echo "4) ".$questionTbl[4]['libelle'];
echo "<button class='init'>Remettre à zéro</button>";
echo "</div>";
echo "<div class='answerCol4'>";
for ($i=0;$i<sizeof($ans);$i++){
    if (isset($request[3][$i])){$val=$request[3][$i];}else{$val=0;}
    echo "<div class='mybtn'><div>".$ans[$i][2]."</div><div><button type='button'  >".$val."</button>";
    
    echo "<input type='hidden' name='an[3][]'id='an[3][$i]' value='".$val."'></input>";
    echo "</div></div>";
}
echo "</div>";

echo "<div class='question'>5) Avez vous des remarques ou des suggestions?</div>";
echo "<div class='answer answerCenter'><textarea rows=2 column=140 name='comment[1]'></textarea></div>";
echo "</div>";
echo "<button >Soumettre</button>";


echo "</form>";
?>
<script>
$(document).ready(function(){
    $(".answerCol4").on("click","button",function(){
        var all=$(this).parents(".answerCol4").children();
        console.log("all="+$(this).parents(".answerCol4").children().html());
        val=$(this).val();
        i=0;
        exit=false;
        while (!exit){
            exit=false;
            i=i+1
            found=false;
            all.each(function(index){
                if ($(this).children().children().html()==i){
                    found=true;
                    console.log("i="+i+"|"+$(this).children().html());
                }
            });
            if (i>4){exit=true;};
            if (!found){exit=true;}
        }
        if (i<=4){
            console.log("changed");
            $(this).html(i);
            console.log("input="+$(this).parent().first().children().eq(1).val(i));
        }
        console.log(i);  
    });
    $(".init").click(function(){
        console.log("click init");
        var all=$(".answerCol4").children();
        all.each(function(index){
            console.log($(this).children().eq(1).children().eq(1).val(0));
            $(this).children().first().html(0);
            $(this).children().eq(1).val(0);
        });
    });

});



</script>
</body>
</html>


