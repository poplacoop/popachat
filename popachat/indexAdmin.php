<?php
@session_start();
$_SESSION['rootPath']=getcwd();
require_once("./0021-functions/0500-menusFunctions.php");
include "./0021-functions/0501-retrieveFunctions.php";
include "./0021-functions/0505-miscellaneousFunctions.php";
//----------------------------------
// display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//----------------------------------
include "0000-head.php";
include "0002-login.php";
// go to producteur immediatly
//header("Location:0005-producteurs/index.php");
echo myheader();

echo "<body>";
$userRights=$_SESSION['userInfo']['userRights'];
//echo ($userRights);
include '0001-menus.php';
//echo $userRights;

echo "<body>
    <div class='topBanner'>";
echo menu($userRights,'');
echo "</div>";
echo "<h1> Bienvenu(e) sur le site pop Achat</h1>";
echo "<p> Le site a pour objectif d'aider les producteurs à passer leurs commandes à distance en ayant accès aux ventes, aux stocks, aux produits</p>";
echo "<p>Si vous n'avez pas de menu à gauche, n'hésitez pas à me demander des droits d'accès.</p>

<p><a href='mailto:didier.cransac@gmail.com'>didier.cransac03@gmail.com</a></p>";

echo "<p><i>great oaks from little acorns grow</i></p>";

echo "<a href='0003-documents/index.php'>Documentation Aemsoft</a>";

echo "</div>";
echo "</body>
</html>";
?>
