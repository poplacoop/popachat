<?php
@session_start();
//var_dump($_SESSION);
$_SESSION['userInfo']['admin']=0;
$_SESSION['rootPath']="";
include "../0000-head.php";
//include "../0002-login.php";
include "../0021-functions/0501-retrieveFunctions.php";
include "../0021-functions/600-sendmail.php";
$addPath="./";

if (isset($_REQUEST['email'])){
    $query="SELECT pseudo,password,email FROM prod_user where email='".$_REQUEST['email']."'";
    $row=getFirstLine($query);
    //echo $query;
    if ($row!=""){
        $to=$row['email'];
        $cc="";
        $bcc="didier.cransac03@gmail.com";
        $subject="Renvoi du mot de passe";
        $msg="Pour vous connecter a <href='popachat.ml'>popachat.ml</href></p>
        <p>Votre pseudo:".$row['pseudo']."</p>
        <p>Votre mot de passe:".$row['password']."</p>";
        sendMail("didier.cransac03@gmail.com",$to,$cc,$bcc,$subject,$msg);
    }
}

echo myheader("<link rel='stylesheet' href='./documentation/style.css?v1.1'>",$addPath);
echo "<body>";


echo "<h1> Page de récupération du mot de passe</h1>";

echo "<form>";
echo "<p>Veuillez saisir votre adresse mail.</p>
<p>Votre mot de passe vous sera renvoyé.</p>";
echo "
<div>
<p><input name=email ></input></p>";
echo "<button type='submit'>Envoyer le mot de passe</button>";
echo "</form><br>";
echo "<a href='../index.php'>Revenir à la page de connexion</a>";
echo "</body>
</html>";
?>
