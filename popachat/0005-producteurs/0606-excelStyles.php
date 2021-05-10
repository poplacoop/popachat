<?php

// Border Thin
   $styleThinBorder = array(
        'borders' => array(
            'outline' => array(
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => array('rgb' => '00000000'),
            ),
        ),
    );
    

//-----------------------------------------------------------------------
// style grid
    
    $mycolor="dce6f2";
    $borderStyleSet=[
        // (C3) BORDER
      'borders' => [
            'top' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              'color' => ['rgb' => '000000']
            ],
            'bottom' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              'color' => ['rgb' => '000000']
            ],
            'left' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              'color' => ['rgb' => '000000']
            ],
            'right' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              'color' => ['rgb' => '000000']
            ]
         ]        
    ];
//-----------------------------------------------------------------------
// style bold 14
    $boldFontstyleSet = [
  // (C1) FONT
  'font' => [
    'bold' => true,
    //'italic' => true,
    //'underline' => true,
    //'strikethrough' => true,
    //'color' => ['argb' => 'FFFF0000'],
    //'name' => "Cooper Hewitt",
    'size' => 14
  ]];
//----------------------------------------------------------------------
// style border Outline
$thickOutlinestyleSet = [
   //ALTERNATIVELY, THIS WILL SET ALL
    'outline' => [
        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
        'color' => ['argb' => '00000000']
        ]
    ];
    
//----------------------------------------------------------------------
// style size 16 horizontal vertical center and fill color
$styleSet = [
  // (C1) FONT
  'font' => [
    //'bold' => true,
    //'italic' => true,
    //'underline' => true,
    //'strikethrough' => true,
    //'color' => ['argb' => 'FFFF0000'],
    //'name' => "Cooper Hewitt",
    'size' => 16
  ],

  // (C2) ALIGNMENT
  'alignment' => [
    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
  ],

  // (C3) BORDER
  /*'borders' => [
    /*'top' => [
      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      'color' => ['argb' => 'FFFF0000']
    ],
    'bottom' => [
      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      'color' => ['argb' => 'FF00FF00']
    ],
    'left' => [
      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
      'color' => ['argb' => 'FF0000FF']
    ],
    'right' => [
      'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
      'color' => ['argb' => 'FF0000FF']
    ]*/
     //ALTERNATIVELY, THIS WILL SET ALL
  /*  'outline' => [
        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
        'color' => ['argb' => '00000000']
    ]
  ],*/

  // (C4) FILL
  'fill' => [
    // SOLID FILL
    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    'color' => ['rgb' => $mycolor]

    /*  GRADIENT FILL
    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
    'rotation' => 90,
    'startColor' => [
        'argb' => 'FF000000',
    ],
    'endColor' => [
        'argb' => 'FFFFFFFF',
    ]*/
  ]
];
   
//----------------------------------------------------------------------
// style outline   
    $styleArray = array(
        'borders' => array(
            'outline' => array(
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => array('rgb' => '00000000'),
            ),
        ),
    );
    
    //$Sheet ->getStyle("".chr(ord('A')+($col))."1")->applyFromArray($styleArray);
   
   
        // LOGO
    //$pdf->Image('../0101-images/popLogo.jpg',10,10,15);
    // Arial bold 15


    
    $borderStyleSet=[
    // (C3) BORDER
  'borders' => [
        'top' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['rgb' => '000000']
        ],
        'bottom' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['rgb' => '000000']
        ],
        'left' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['rgb' => '000000']
        ],
        'right' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['rgb' => '000000']
        ]
     ]        
    ];
//-----------------------------------------------------------------------
// fill with color
$colorStyleSet = [
 
  // (C4) FILL
  'fill' => [
    // SOLID FILL
    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    'color' => ['rgb' => $mycolor]

    /*  GRADIENT FILL
    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
    'rotation' => 90,
    'startColor' => [
        'argb' => 'FF000000',
    ],
    'endColor' => [
        'argb' => 'FFFFFFFF',
    ]*/
  ]
]; 
?>  
