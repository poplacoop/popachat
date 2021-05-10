<?php
@session_start();
require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0501-retrieveFunctions.php";
include "../0021-functions/0505-miscellaneousFunctions.php";
include "../0000-head.php";
include "../0002-login.php";
include "../0001-menus.php";
$userRights=$_SESSION['userInfo']['userRights'];

echo myheader();
echo "<body>
    <div class='topBanner'>";

echo menu($userRights,'');

echo "<h1>Site des producteurs</h1>
<p>Que me proposez-vous pour peupler cette page?</p>
<p>Si vous ne pouvez accéder qu'à cette page, n'hésitez pas à me demander des droits d'accès.</p>
<p><a href='mailto:didier.cransac@gmail.com'>didier.cransac@gmail.com</a></p>";
?>
