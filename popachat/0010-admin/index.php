<?php
@session_start();
include "0002-introAdmin.php";

echo "<body>
    <div class='topBanner'>";
echo menuAdmin($menuFilter);
echo "</div>";
echo "</body>
</html>";
?>
