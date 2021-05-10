<?php
//----------------------------------------------------------------------
//   Export EXCEL
//----------------------------------------------------------------------

    require_once rootPath.'/0022-vendor/autoload.php';
  
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;    
    // prepare xls

include "0606-excelStyles.php";
//----------------------------------------------------------------------
function setCell($sheet,$row,$col,$cell){
    $sheet->setCellValue(cell($row,$col), $cell);
}
function formatCell(&$Sheet,$rowInit,$colInit,$rowEnd,$colEnd,$cellFormat){
    $ref=chr(ord('A')+$colInit).$rowInit.":".chr(ord('A')+$colEnd).$rowEnd;
    //echo $ref;
    $style = $Sheet->getStyle($ref);
    $style->applyFromArray($cellFormat);
}

//----------------------------------------------------------------------
$displayTotal=1;
//var_dump($field);
    $Wbook = new Spreadsheet();
    $Sheet = $Wbook->getActiveSheet(); 
    
    // headers
    $colLeft=0;
    $colRight=3;

    $col=0;
 
    // put in bold
    $style = $Sheet->getStyle('A3:B7');
    $style->applyFromArray($boldFontstyleSet);

    $Sheet->getRowDimension(1)->setRowHeight(32); 
    $Sheet->mergeCells("A1:E1");
    $Sheet->setCellValue(cell(1,0), 'BON DE COMMANDE');

    $style = $Sheet->getStyle('A1');
    $style->applyFromArray($styleSet); 
    $style = $Sheet->getStyle('A1');
    $style->applyFromArray($borderStyleSet); 
    
    
    $row=2;
    $cell="Commande no ".$field['id'];
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell="Le ".convert_date($field['date_envoi']); // adresse fournisseur
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    //line 3 - col 1
    $row=3;
    $cell="Livraison le ".convert_date($field['date_livraison_prevue'],1);
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell=utf8_decode($field['titre']); // adresse fournisseur
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 4 - col 1
    $cell="Matin (8h30 - 12h30)";
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell=utf8_decode($field['adresse1']);
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 5
    $cell=$field['popnom'];  
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    //echo "<br>toto".utf8_decode($field['adresse2']);
    $cell=utf8_decode($field['adresse2']);
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 6
    $cell=utf8_decode($field['popadresse1']);
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell=utf8_decode($field['adresse3']);
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    //line 7
    $cell=utf8_decode($field['popadresse2']);
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    //echo "<br>telephone is ".$field['telephone'];
    $cell=$field['telephone'];
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    //echo "<br>contact is ".$field['contact'];
    //line 8
    $cell="Contact: ".$referentPop1;
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell="".$field['contact'];
     ///echo $cell;
    if ($cell==""){$cell="";}
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    //line 9
    $row=9;
    $cell="Contact: Hélène Quévremont";
    $Sheet->setCellValue(cell($row,$colLeft), $cell);
    
    $cell="".$field['email'];
    $Sheet->setCellValue(cell($row++,$colRight), $cell);
    
    // line 11
    $row=11;
    $headers=["Réf.","Libellé","Quantité","Prix unitaire","Montant"];
    $col=0;
    foreach ($headers as $cell){
        $style = $Sheet->getStyle(cell($row,$col));
        $style->applyFromArray($colorStyleSet); 
        $Sheet->setCellValue(cell($row,$col++), $cell);

    }
   
    $row++;
    $str="<table>\n";
    $str.="<tr>";

    
    // main 

    $total=0; 
    for ($k=1;$k<sizeof($commandeListTable);$k++){
        $ean=$commandeListTable[$k]['ean'];
        $rightEan=substr($ean,strlen($ean)-6);
        //$Sheet->setCellValue(cell($row,0), substr($commandeListTable[$k]['designation'],0,45));$row+1;
        // display designation
        $rowList=[$commandeListTable[$k]['refFour'],$commandeListTable[$k]['designation'],
        $commandeListTable[$k]['quantite']/$commandeListTable[$k]['conditionnement']];
        $col=0;
        
        foreach ($rowList as $cell){
            $style = $Sheet->getStyle(cell($row,$col));
            $style->applyFromArray($borderStyleSet); 
            $Sheet->setCellValue(cell($row,$col++), $cell);
            $str.="<td>".convert_number($row[$k])."</td>";
            
        }
        
        
        // exclude if 000000
        if ($rightEan!="000000"){
            
            $rowList=[$commandeListTable[$k]['prixAchat'],mynumber_format($commandeListTable[$k]['quantite']*$commandeListTable[$k]['prixAchat'],2)];
            foreach ($rowList as $cell){
                $style = $Sheet->getStyle(cell($row,$col));
                $style->applyFromArray($borderStyleSet); 
                $Sheet->setCellValue(cell($row,$col++), $cell);
                $str.="<td>".convert_number($row[$k])."</td>";
            }
        }
        else{
            $displayTotal=0;
        }
        
        
        $row++;
        $total+=$commandeListTable[$k]['quantite']*$commandeListTable[$k]['prixAchat'];
        $str.="</tr>";
    }
    
    
    // Total line
    if ($displayTotal){
        $cell="Montant Total";
        $Sheet->setCellValue(cell($row,$col-2), $cell);
        
        $cell=convert_number($total);
        $Sheet->setCellValue(cell($row,$col-1), $cell);
        
        $str.="<tr><td></td><td></td><td></td><td>".convert_number($total)."</td>";
        
        formatCell($Sheet,$row,$col-2,$row,$col-1,$boldFontstyleSet);
        formatCell($Sheet,$row,$col-2,$row,$col-1,$borderStyleSet);
        $style = $Sheet->getStyle('D15:E15');
        $Sheet->getRowDimension($row)->setRowHeight(18);
        //$style->applyFromArray($boldFontstyleSet);
    }

    
    $str.="</table>";
  
    //$Sheet->getColumnDimensionByColumn(2)->setAutoSize(true);
    //$Sheet->getColumnDimensionByColumn(3)->setAutoSize(true);
    // set column width
    // to convert inch to number coef: x12.7
    $Sheet->getColumnDimensionByColumn(1)->setWidth('8');
    $Sheet->getColumnDimensionByColumn(2)->setWidth('37');
    $Sheet->getColumnDimensionByColumn(3)->setWidth('12');
    $Sheet->getColumnDimensionByColumn(4)->setWidth('16');
    $Sheet->getColumnDimensionByColumn(5)->setWidth('16');
  
    $style = $Sheet->getStyle('A6:B7');
    $Sheet->getRowDimension(6)->setRowHeight(20);
    $Sheet->getRowDimension(7)->setRowHeight(20);
    $Sheet->getRowDimension(4)->setRowHeight(20);
    $Sheet->getRowDimension(5)->setRowHeight(20);
    $Sheet->getRowDimension(3)->setRowHeight(20);
  
    $Sheet ->getStyle('A1:E'.($k-1))->applyFromArray($styleArray);
    //var_dump($dico);
    
    
    $writers = new Xls($Wbook);
    
    //filename
    $outputFileName=$outputFileNameRoot.".xls";
    $fullOutputFileName = './files/'.$outputFileName;
    //echo $fileName;
    $writers->save($fullOutputFileName);
    
    //return $str;

     
    
    
        

