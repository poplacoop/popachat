<?php
function listeSelect($dico,$tableSuffix,$nameRequest,$selectedId,$existEmpty=0,$order=""){
    // create a list with name $nameRequest, $selectedId as selected. 
    // $dico is based on id 
    $str="<select name='$nameRequest' id='$nameRequest' size='1' onchange='submit()'>";
    if ($existEmpty){
        if (""==$selectedId){$checked="selected";}else{$checked="";};
        $str.= "<option value='' $checked></option>\n";}
    foreach($dico[$tableSuffix.$order] as $idx=>$titre){
        
        if ($idx==$selectedId){$checked="selected";}else{$checked="";};
        $str.= "<option value='".$idx."' $checked>".$idx."-".$titre."</option>\n";
    }
    $str.="</select>";
    return $str;
}
    

function tableChoose($dico,$tableSuffix,$selectedId,$ordre=""){
    //$dico is general dico
    // name of suffix for the table
    // has one been selected: $selectedId
    // $ordre='alf' if alphabetic if not $ordre=''
    
    $str="";
    foreach($dico[$tableSuffix.$ordre] as $idx=>$titre){
        if ($idx==$selectedId){$class="class='chosen'";}else{$class="";};
        $str.= "<tr>
        <td><button name='$tableSuffix' value='".$idx."' $class>".$idx."</button></td>
        <td><button name='$tableSuffix' value='".$idx."' $class>".$titre."</button></td></tr>";
    }
    
    return $str;
}
?>
