<?php
function menuSynthese($menuFilter){

    $menuList=['s'=>['synthese','./0004-synthese'],'r'=>['retour','../indexAdmin']];  
        $str="

    <nav class='leftNav'>";
    $filterLetters=str_split($menuFilter);
    //dispArray($letters);
    foreach ($menuList as $letter=>$val){
        //mp($letter);
        //dispArray($filterLetters);
        $class='navActive';
        $str.= "   <div class='$class' ><a href='".$menuList[$letter][1].".php'>".$menuList[$letter][0]."</a></div>";
    }
    
    
    return $str;
}

?>
