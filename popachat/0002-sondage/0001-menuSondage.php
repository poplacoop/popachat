<?php
function menuProd($menuFilter){

    $menuList=['s'=>['cosmetiques','/0002-sondage/0010-cosmetiques']];  
        $str="

    <nav class='leftNav'>";
    $filterLetters=str_split($menuFilter);
    //dispArray($letters);
    foreach ($menuList as $letter=>$val){
        //mp($letter);
        //dispArray($filterLetters);
        if (in_array($letter,$filterLetters)){
            $class='navActive';
        }
        else{
            $class='navDisabled';
        }
        $str.= "   <div class='$class' ><a href='".$menuList[$letter][1].".php'>".$menuList[$letter][0]."</a></div>";
    }
    
    
    return $str;
}

?>
