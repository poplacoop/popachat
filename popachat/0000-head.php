<?php

function myheader($ajout="",$addPath=""){
    $path="";
    if (strlen(getcwd())!=strlen($_SESSION['rootPath'])){
        $path="../";
    }
    $path.=$addPath;    
    
    $str="<!DOCTYPE html >
<html>

<head>
	<title>Pop Achat</title>
	<meta http-equiv='content-type' content='text/html;charset=utf-8' />
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css' 
    rel='stylesheet' integrity='sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl' 
    crossorigin='anonymous'>
    <script src='".$path."js/jquery-3.5.1.min.js'></script>";
    $str.="<script src='".$path."js/functions.js'></script>";

   // <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css' integrity='sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh' crossorigin='anonymous'>
   //$str.=" <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl' crossorigin='anonymous'>";
    //$str.="<link rel='stylesheet' href='/js/bootstrap.min.css' rel='stylesheet' />";
    //$str.="<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js' integrity='sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0' crossorigin='anonymous'></script>";
//<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
    $str.=$ajout;
   $str.=" <link rel='stylesheet' href='".$path."style.css?v1.1'>
    <link rel='stylesheet' href='".$path."0901-navStyle.css?v1.2'>

     <link rel='stylesheet' href='".$path."0005-producteurs/0902-prodStyle.css?v1.2'>
        <link rel='stylesheet' href='".$path."0005-producteurs/0901-prodStyleTable.css?v1.1'>
    <link rel='icon' type='image/ico' href='".$path."0101-images/favicon.ico' />
    <script src='".$path."js/Chart.min.js'></script>
</head>";
    return $str;
}

?>
