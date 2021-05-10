<?php
@session_start();
include "0002-introAdmin.php";
echo "<body>
    <div class='topBanner'>";
echo menuAdmin($menuFilter);
echo "</div>";

include "0003-prepareAdminData.php";

require '../0022-vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;    
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;      

include "0510-imports_functions.php";
include "0511-imports_fromTableKeysFunction.php";
include "0102-import-generic.php";


include "../0005-producteurs/0603-recomputePrices.php";


?>
