<?php


//var_dump($field);
//filename
$outputFileName=$outputFileNameRoot.".pdf";
$fullOutputFileName = './files/'.$outputFileName;
//echo $outputFilename;
//var_dump($chosenCommandeDico);

//$titles=$chosenCommandeTable[0];
//$value=$chosenCommandeTable[1];
//----------------------------------------------------------------------
//$field=[];
//foreach( $titles as $key=>$val){
//    $field[$val]=$value[$key];
//}

//displayTableInHtml($chosenCommandeTable);

$query="SELECT * FROM prod_referencesPop WHERE validation=1";
$popTable=query_table($query);
$titles=$popTable[0];
for ($k=1;$k<sizeof($popTable);$k++){
    $field["pop".$popTable[$k]['keyword']]=$popTable[$k]['content'];
}

// Get identity of Author
//displayTableInHtml($commandeListTable);
//displayTableInHtml($popTable);
$query="SELECT * FROM prod_user WHERE id='".$field['author']."'";
$userTable=query_table($query);
//displayinhtml($userTable);

$fournisseurQuery="SELECT * FROM prod_fournisseur WHERE id='".$order['fournisseur']."'";
$fournisseurTable=query_table_dico($fournisseurQuery);

foreach($fournisseurTable[0] as $key=>$cell){
    $field[$key]=$cell;
}
//echo "field<br>";
//var_dump($field);
//echo "<br>ficnish<br>";
// récupérer les référents pop
$dicoFournisseur=query_table_dico($fournisseurQuery);
if ($dicoFournisseur[0]['referentPop2']!=""){
    $query="SELECT * FROM prod_user where id=".$dicoFournisseur[0]['referentPop']." OR id=".$dicoFournisseur[0]['referentPop2'];
}
else{
    $query="SELECT * FROM prod_user where id=".$dicoFournisseur[0]['referentPop'];
}
$dicoUser=query_table_dico($query);
$referentPop1=$dicoUser[0]['email'];
$referentPop2="";
if (isset($dicoUser[1]['email'])){$refPop2=$dicoUser[1]['email'];}

//$query="select * from prod_user where id=".$fournisseurTable[0]['referentPop2'];
//$table=query_table($query);

//$refPop2="";
//if(sizeof($table)>1){
//    $refPop2=$table[1]['prenom']." ".$table[1]['nom'];
//}


//$field['titre']=$fournisseurTable[1][1];
//displayTableInHtml($fournisseurTable);
//for ($k=1;$k<5;$k++){
//    $field['adresse'.$k]=$fournisseurTable[1][$k+1];
//}

//$field['telephone']=$fournisseurTable[1]['telephone'];
//print_r($fournisseurTable[1]);

$field['userNom']=$userTable[1]['nom'];
$field['userPrenom']=$userTable[1]['prenom'];
$field['userTelephone']=$userTable[1]['telephone'];
$field['userEmail']=$userTable[1]['email'];
//dispArray($field);

require '../0022-vendor/autoload.php';
use Fpdf\Fpdf;

class PDF extends FPDF {


/*
function Header() {
 // Logo
    $this->Image('popLogo.jpg',10,10,15);
    // Arial bold 15
    $this->SetFont('Arial','B',20);
    // Move to the right
    $this->Cell(50,20);
    // Title
    $this->SetFillColor(100, 255, 255);
    $this->Cell(100,10,'BON DE COMMANDE',1,0,'C',1);
    // Line break
    $this->Ln(10);
}
*/
 
function Footer() {
//This is the footer; it's repeated on each page.
//enter filename: phpjabber logo, x position: (page width/2)-half the picture size,
//y position: rough estimate, width, height, filetype, link: click it!
    $this->SetY(-30);
    $this->Ln(10);
    $this->Cell(0,10,utf8_decode("POP LA COOP - Société coopérative d intérêt collectif par actions simplifiée à capital variable"),0,0,'C');
    $this->Ln(5);
    $this->Cell(0,10,utf8_decode("46 Chemin de Montval à la Montagne - 78160 Marly le Roi - N° SIREN : 848 886 966"),0,0,'C');
    $this->Ln(10);
}
 
}

$lineBreak=5;

//class instantiation
$pdf=new PDF("P","mm","A4");
$pageWidth=210;
$halfWidth=floor($pageWidth/2);
 
$pdf->SetMargins(20,20,20,20);
 
$pdf->AddPage();

    // LOGO
    $pdf->Image('../0101-images/popLogo.jpg',10,10,25);
    // Arial bold 15
    $pdf->SetFont('Arial','B',15);

    // col 2
    $pdf->SetX(70);
    $pdf->Cell($halfWidth,10,'Bon de Commande','TRLB','','C',0);
    $pdf->Ln(15);
    //line 3 - col 1
    $pdf->SetFont('Times','',12);
    $pdf->Cell($halfWidth-30, 12, $field['popnom'],'LRT','', "L");  //nom
    
    $pdf->SetX($halfWidth);
    $pdf->Cell($halfWidth+15, 12, utf8_decode($field['titre']),'','', "L"); // adresse fournisseur
    
    $pdf->Ln($lineBreak);
    
    // line 4 - col 1
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($halfWidth-30, 12, utf8_decode($field['popadresse1']),'RL', '',"L");
    $pdf->SetFont('Times','',12);
    
    $pdf->SetX($halfWidth);
    $pdf->Cell($halfWidth+15, 12, utf8_decode($field['adresse1']),'','', "L");
        
    $pdf->Ln($lineBreak);
    
    
    // line 5
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($halfWidth-30, 12, utf8_decode($field['popadresse2']),'LRB','', "L");
    $pdf->SetFont('Times','',12);
    
    $pdf->SetX($halfWidth);
    $pdf->Cell($halfWidth+15, 12, utf8_decode($field['adresse2']),'','', "L");
    $pdf->Ln($lineBreak);

    // line 6
    $pdf->Cell($halfWidth-30, 12, "",'','', "");
    $pdf->SetX($halfWidth);
    $pdf->Cell($halfWidth+15, 12, utf8_decode($field['adresse3']),'','', "L");
    $pdf->Ln(15);


    // line 7

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($halfWidth,10,"Livraison le ".convert_date($field['date_livraison_prevue'],1),'LRT',0,'L',0);
    $pdf->SetFont('Times','',12);
    
    $pdf->SetX($halfWidth+30);
    $pdf->Cell($halfWidth+15, 10, utf8_decode($field['contact']),'', "L");
    $pdf->Ln($lineBreak);
       
    
    //line 8
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell($halfWidth,10,"Matin (8h30 - 12h30)",'LRB',0,'L',0);
    $pdf->SetFont('Times','',12);
    
    $pdf->SetX($halfWidth+30);
    if ($field['telephone']!=""){
        $pdf->Cell($halfWidth, $lineBreak, "Telephone:".$field['telephone'],'', "L");
    }
    $pdf->Ln(12); 
  
    //line 8
    
    //$pdf->Cell($halfWidth, $lineBreak, "Contact:".$field['userPrenom']." ".$field['userNom'],'', "L");
    $pdf->Cell($halfWidth, $lineBreak, "Contact:".$referentPop1,'', "L");
    
    $pdf->SetX($halfWidth+30);
    $pdf->Cell($halfWidth+15, $lineBreak, $field['contact'],'', "L");
    
    $pdf->Ln($lineBreak);  
    
    
    //line 9  
    $pdf->Cell($halfWidth, $lineBreak, utf8_decode("Contact:Hélène Quévremont"),'', "L");
    $pdf->SetX($halfWidth+30);
    $pdf->Cell($halfWidth, $lineBreak, $field['email'],'', "L");
    $pdf->Ln($lineBreak); 
    
    // line 10
    $pdf->Cell($halfWidth, $lineBreak, utf8_decode("Téléphone: ").$field['poptelephone'],'', "L");
    $pdf->Cell($halfWidth, $lineBreak, "",'', "L");
    $pdf->Ln($lineBreak); 

    // line 11
    $lineBreak=7;
    // Les titres
    $pdf->Ln($lineBreak*1.5);
    $pdf->SetFont('Times','',9);
    $columnWidth=['left'=>10,'designation'=>80,'refFour'=>20,'colis'=>15,'conditionnement'=>15,'quantite'=>15,'prixAchat'=>20,'montant'=>25];
    $totalWidth=$columnWidth['designation'];
    foreach($columnWidth as $key=>$val){
        if (in_array($key,$print)){
            //echo "$key<br>";
            $totalWidth+=$columnWidth[$key];
        }
    }
    //echo $totalWidth;
    $maxWidth=190;
    $columnWidth['designation']=min($maxWidth-$totalWidth+$columnWidth['designation'],110);
    //$columnWidth['designation']=40;
    //foreach($columnWidth as $key=>$val){
    //    if (in_array($key,$print)){
    //        $columnWidth[$key]=$columnWidth[$key]/$totalWidth*$maxWidth;
    //    }
    //}
    
    
    

    $pdf->setX($columnWidth['left']);
    $pdf->Cell($columnWidth['designation'],$lineBreak,"","LRTB");
    if (in_array('refFour',$print)){
        $pdf->Cell($columnWidth['refFour'],$lineBreak,utf8_decode("Réf."),"LRTB",0,'C');
    }
    if (in_array('colis',$print)){
            $pdf->Cell($columnWidth['colis'],$lineBreak,"Colis","LRTB",0,'C');  
    }
    if (in_array('conditionnement',$print)){
        $pdf->Cell($columnWidth['conditionnement'],$lineBreak,utf8_decode("Cond."),"LRTB",0,'C');
    }
    if (in_array('quantite',$print)){
        $pdf->Cell($columnWidth['quantite'],$lineBreak,utf8_decode("Quantité"),"LRTB",0,'C');
    }
    if (in_array('prixAchat',$print)){
        $pdf->Cell($columnWidth['prixAchat'],$lineBreak,utf8_decode("Prix"),"LRTB",0,'C');
    }
    if (in_array('montant',$print)){
        $pdf->Cell($columnWidth['montant'],$lineBreak,utf8_decode("Montant"),"LRTB",0,'C');
    }
    $pdf->Ln($lineBreak);
    
    
    // le tableau des commandes
    $total=0;
    for ($k=1;$k<sizeof($commandeListTable);$k++){
        $ean=$commandeListTable[$k]['ean'];
        $rightEan=substr($ean,strlen($ean)-6);
        //echo $rightEan."=";
        $pdf->setX(10);
        //echo strlen($commandeListTable[$k]['designation'])."<br>";
        //echo $columnWidth['designation'];
        
        $originalDesignation=$commandeListTable[$k]['designation'];
        $designation=substr($originalDesignation,0,floor($columnWidth['designation']/2)-4);
        if($designation!=$originalDesignation){$designation.="......";}
        $pdf->Cell($columnWidth['designation'],$lineBreak,$designation,"LRTB",0,'L');
        
        if (in_array('refFour',$print)){
            $pdf->Cell($columnWidth['refFour'],$lineBreak,$commandeListTable[$k]['refFour'],"LRTB",0,'C');
        }
        //if (in_array('colis',$print)){
        //    $pdf->Cell($columnWidth['colis'],$lineBreak,number_format($commandeListTable[$k]['quantite']/$commandeListTable[$k]['conditionnement'],0),"LRTB",0,'C');  
        //}
        
        if ($rightEan!="000000"){
            if (in_array('colis',$print)){
                $pdf->Cell($columnWidth['colis'],$lineBreak,number_format($commandeListTable[$k]['quantite']/$commandeListTable[$k]['conditionnement'],0),"LRTB",0,'C');
            }
            
            
        }
        else{
            if (in_array('colis',$print)){
                $pdf->Cell($columnWidth['colis'],$lineBreak,number_format($commandeListTable[$k]['quantite']/$commandeListTable[$k]['conditionnement'],0),"LRTB",0,'C');
            }
            $displayTotal=0;
        }
        if (in_array('conditionnement',$print)){
            $pdf->Cell($columnWidth['conditionnement'],$lineBreak,$commandeListTable[$k]['conditionnement'],"LRTB",0,'C');  
        }
                if (in_array('quantite',$print)){
            $pdf->Cell($columnWidth['quantite'],$lineBreak,$commandeListTable[$k]['quantite'],"LRTB",0,'C');  
        }
        if (in_array('prixAchat',$print)){
                $pdf->Cell($columnWidth['prixAchat'],$lineBreak,$commandeListTable[$k]['prixAchat'],"LRTB",0,'C');
        }
        if (in_array('montant',$print)){
            $pdf->Cell($columnWidth['montant'],$lineBreak,mynumber_format($commandeListTable[$k]['quantite']*$commandeListTable[$k]['prixAchat'],2)." ".chr(128),"LRTB",0,'R');
        }
        $total+=$commandeListTable[$k]['quantite']*$commandeListTable[$k]['prixAchat'];
        $pdf->Ln($lineBreak);

    }


if (in_array('montant',$print)){
    
    
    
    $pdf->setX($columnWidth['left']);
    $pdf->Cell($columnWidth['designation'],$lineBreak,"Total HT","LRTB");
    foreach($print as $key){
        if ($key!='montant'){
            $pdf->Cell($columnWidth[$key],$lineBreak,"","LRTB");
        }
    }
    
    $pdf->Cell($columnWidth['montant'],$lineBreak,mynumber_format($total,2)." ".chr(128),"LRTB",0,"R");
    
    $pdf->Ln($lineBreak);
}
    
    $doc=$pdf->Output('F',$fullOutputFileName);
    //echo $outputFilename." a été sauvegardé";
    //$imagick = new Imagick();
     // Reads image from PDF
    //$imagick->readImage($fullFilename.".pdf");
     // Writes an image or image sequence Example- converted-0.jpg, converted-1.jpg
     //unlink($fullFilename.".jpg");
     //$imagick->writeImages($fullFilename.".jpg", false);
    //echo "<img src='./images/converted.jpg'/>";

    




?>
