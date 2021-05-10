<?php
//----------------------------------------------------------------------
//   Export EXCEL
//----------------------------------------------------------------------
    require_once rootPath.'/0022-vendor/autoload.php';
  
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;    
    // prepare xls
function exportToXls($fileName,$tbl,$addEmptyColumns=[]){

    $Wbook = new Spreadsheet();
    $Sheet = $Wbook->getActiveSheet(); 

    $styleArray = array(
        'borders' => array(
            'outline' => array(
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => array('argb' => '00000000'),
            ),
        ),
    );
   
    $str="<table>\n";
    $str.="<tr>";
    // headers
    $col=0;
    foreach ($tbl[0] as $cell){
        $Sheet->setCellValue(cell(1,$col), $cell); //xls
        $Sheet ->getStyle("".chr(ord('A')+($col))."1")->applyFromArray($styleArray);
        $col=$col+1;
        $str.="<th>$cell</th>";
        
    }
    foreach ($addEmptyColumns as $newcol){
        $Sheet->setCellValue(cell(1,$col), $newcol); //xls
        $str.="<th>".$newcol."</th>";
        $col=$col+1;
    }
    //$Sheet ->getStyle("".chr(ord('A')+($col))."1")->applyFromArray($styleArray);
    
    $str.= "</tr>";

    // main    
    
    for($i=1;$i<sizeof($tbl);$i++){
        
        $row=$tbl[$i];
        //dispArray($row);
        $str.="<tr>";
        $col=0;
        for($k=0;$k<sizeof($row)/2;$k++){
            $Sheet->setCellValue(cell($i+1,$col), $row[$k]);
            $Sheet ->getStyle(cell($i+1,$col))->applyFromArray($styleArray);
            //$Sheet ->getStyle("".chr(ord('A')+($col)).($i+1)."")->applyFromArray($styleArray);
            $col=$col+1; //xls
            $str.="<td>".convert_number($row[$k])."</td>";
        }
        //echo "".chr(ord('A')+($col+sizeof($addEmptyColumns)-1)).($i)."<br>";
        
        foreach ($addEmptyColumns as $newcol){
            $Sheet->setCellValue(cell($i+1,$col), ""); //xls
            $refAddedCell=chr(ord('A')+($col)).($i);
            $Sheet ->getStyle($refAddedCell)->applyFromArray($styleArray);
            $str.="<td></td>";
            $col=$col+1;
        }
        
        $str.="</tr>";
        
        
    }
    $str.="</table>";
  
    //$Sheet->getColumnDimensionByColumn(2)->setAutoSize(true);
    //$Sheet->getColumnDimensionByColumn(3)->setAutoSize(true);
    // set column width
    $Sheet->getColumnDimensionByColumn(1)->setWidth('20');
    $Sheet->getColumnDimensionByColumn(2)->setWidth('30');
    $Sheet->getColumnDimensionByColumn(3)->setWidth('10');
  
    //$Sheet ->getStyle('A1:D'.($k-1))->applyFromArray($styleArray);
  
  
    
    $writers = new Xls($Wbook);
    $fileName = "./files/".$fileName;
    $writers->save($fileName);
    
    return $str;
    
     
    
    
        
}
