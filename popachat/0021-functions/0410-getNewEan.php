<?php


function new_valid_ean(){
    $query="SELECT substring(ean,1,12) as eanNb,ean FROM `prod_articles` WHERE ean like '2%' order by eanNb";
    $eanDico=create_one_field_dictionnary_sql($query,"ean","eanNb");
    //echo $eanDico["200000000001"];
    $eanInit=1;
    $sortie=0;
    while (!$sortie){
    
        //echo "<br>ean=".$eanInit."/";
        $eanNb="2".sprintf("%011d", $eanInit);
        //echo $eanNb."-";
        //echo "lastValue=".find_last_value($eanNb);
        $newEan=$eanNb.find_last_value($eanNb);
        if (array_key_exists($newEan,$eanDico)){
            //echo "dico=".$eanDico[$newEan];
            }
        else{
            //echo "new=".$eanNb.find_last_value($eanNb);
            $sortie=1;
        }
        $eanInit++;
    }
    return $newEan;
    
}
//echo return_valid_ean();
function find_last_value($eanNb){
    
    $digits=$eanNb;
    // 1. Add the values of the digits in the 
    // even-numbered positions: 2, 4, 6, etc.
    $even_sum = $digits[1] + $digits[3] + $digits[5] +
                $digits[7] + $digits[9] + $digits[11];

    // 2. Multiply this result by 3.
    $even_sum_three = $even_sum * 3;

    // 3. Add the values of the digits in the 
    // odd-numbered positions: 1, 3, 5, etc.
    $odd_sum = $digits[0] + $digits[2] + $digits[4] +
               $digits[6] + $digits[8] + $digits[10];

    // 4. Sum the results of steps 2 and 3.
    $total_sum = $even_sum_three + $odd_sum;

    // 5. The check character is the smallest number which,
    // when added to the result in step 4, produces a multiple of 10.
    $next_ten = 10-$total_sum % 10;
    if ($next_ten==10){$next_ten=0;}
    return $next_ten;
}

?>
