<?php
@session_start();

foreach ($_SESSION as $key=>$value)
    {
        if (isset($_SESSION[$key]))
            unset($_SESSION[$key]);
    }
unset($_SESSION);
unset($_REQUEST);

$order="";
//echo "coucou";
//var_dump($_SESSION);
//var_dump($_REQUEST);
header("location:/index.php");
?>
