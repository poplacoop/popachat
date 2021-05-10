<?php
@session_start();

require_once("../0021-functions/0500-menusFunctions.php");
include "../0021-functions/0501-retrieveFunctions.php";
include "../0021-functions/0505-miscellaneousFunctions.php";
//include "0003-prepareData.php";
//----------------------------------
// display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//----------------------------------
include "../0000-head.php";
include "../0002-login.php";
echo myheader();
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

$menuFilter="mr";




include '0001-menuSynthese.php';
echo "<body>
    <div class='topBanner'>";
echo menuSynthese($menuFilter);
echo "</div>";
echo "</body>
</html>";
?>
