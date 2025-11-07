<?php

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$style = [
  // === FONT ===
  'bold' => [
    'font' => ['bold' => true],
  ],

  // === BORDER ===
  'border' => [
    'borders' => [
      'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
  ],

  // === WRAP TEXT ===
  'wrap' => [
    'alignment' => ['wrapText' => true],
  ],

  // === ALIGNMENT CENTER ===
  'center' => [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_CENTER,
      'vertical'   => Alignment::VERTICAL_CENTER,
    ],
  ],

  // === ALIGNMENT LEFT / RIGHT ===
  'left' => [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_LEFT,
      'vertical'   => Alignment::VERTICAL_CENTER,
    ],
  ],
  'right' => [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_RIGHT,
      'vertical'   => Alignment::VERTICAL_CENTER,
    ],
  ],

  // === TOP ALIGN ===
  'topLeft' => [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_LEFT,
      'vertical'   => Alignment::VERTICAL_TOP,
    ],
  ],
  'topCenter' => [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_CENTER,
      'vertical'   => Alignment::VERTICAL_TOP,
    ],
  ],
  'topRight' => [
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_RIGHT,
      'vertical'   => Alignment::VERTICAL_TOP,
    ],
  ],
];


function applyStyle($sheet, string $range, array $classes)
{
  global $style;
  $merged = [];
  foreach ($classes as $cls) {
    if (isset($style[$cls])) $merged = array_replace_recursive($merged, $style[$cls]);
  }
  $sheet->getStyle($range)->applyFromArray($merged);
}
