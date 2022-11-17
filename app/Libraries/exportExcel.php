<?php

namespace App\Libraries;

use IntlDateFormatter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportExcel
{

    const FORMAT_NUMBER_COMMA_SEPARATED = "#,##0";

    public function __construct($data, $options = [])
    {
        $this->data = $data;
        $this->filename = "excel_" . time();
        if (isset($options['filename']) && !empty($options['filename'])) {
            $this->filename = $options['filename'] . time();
        }
        $this->title = false;
        if (isset($options['title'])  && !empty($options['title'])) {
            $this->title = $options['title'];
        }
        $this->is_multiple_sheet = false;
        if (isset($options['is_multiple_sheet'])) {
            $this->is_multiple_sheet = $options['is_multiple_sheet'];
        }

        $this->spreadsheet = null;
        
        $this->formatter_datetime = new IntlDateFormatter(
            'id_ID', 
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL, 
            null,
            null,
            'dd MMMM yyyy hh:mm:ss'
        );

        // THEME START
        $this->theme = (object)[];
        $this->theme->sheet_title = [
            'font'  => [
                'bold'  => true,
                'color' => [
                    'rgb' => '000000'
                ],
                'size'  => 12,
            ],
        ];
        $this->theme->header = [
            'font'  => [
                'bold'  => true,
                'color' => [
                    'rgb' => '000000'
                ],
                'size'  => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => 'dedede'
                ],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '666666'],
                ],
            ],
        ];
        $this->theme->summary = [
            'font'  => [
                'bold'  => true,
                'color' => [
                    'rgb' => '000000'
                ],
                'size'  => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => 'bfbfbf'
                ],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '666666'],
                ],
            ]
        ];
        $this->theme->data = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '666666'],
                ],
            ],
        ];
        $this->theme->data_main = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '666666'],
                ],
            ],
            'font'  => [
                'bold'  => true,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => 'dbe8cf'
                ],
            ],
        ];
        // THEME END

        // CACHE/MEMORY START
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $this->calculate = (object) [];

    }

    public function exportStandard()
    {

        if (!$this->data || !is_array($this->data)) {
            return false;
        }

        $this->spreadsheet = new Spreadsheet();

        $sheetIndex = $this->spreadsheet->getFirstSheetIndex();
        $this->spreadsheet->removeSheetByIndex($sheetIndex);

        $this->writeSpreadsheet();
        
        $writer = new Xlsx($this->spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function writeSpreadSheet()
    {
        foreach ($this->data as $key => $value) {
            $title = (isset($value['title']) && !empty($value['title'])) ? $value['title'] : 'Ekspor Data';
            $sheet_title = (isset($value['sheet_title']) && !empty($value['sheet_title'])) ? $value['sheet_title'] : "Sheet {$key}";
            $column_arr = (isset($value['column']) && !empty($value['column'])) ? $value['column'] : [];
            $data_arr = (isset($value['data']) && !empty($value['data'])) ? $value['data'] : [];
            $summary_arr = (isset($value['summary']) && !empty($value['summary'])) ? $value['summary'] : [];

            $column_index_count = 0; 

            $this->spreadsheet->createSheet();
            
            $this->spreadsheet->getProperties()->setCreator('SODA POS')
                ->setLastModifiedBy('SODA POS')
                ->setTitle($this->title)
                ->setSubject($this->title)
                ->setDescription("")
                ->setKeywords($this->title);
            
            $sheet = $this->spreadsheet->getSheet($this->spreadsheet->getSheetCount() - 1);
            $sheet->setTitle($sheet_title);
            
            $cell_column = $cell_column_start = $cell_column_end = 'A';
            $cell_row = $cell_row_start = 1;
            
            //write title
            $sheet->getRowDimension($cell_row)->setRowHeight(20);
            $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->sheet_title);
            $sheet->setCellValue($cell_column . $cell_row, strtoupper($title));
            $cell_row++;
            $sheet->setCellValue($cell_column . $cell_row, 'Tanggal Ekspor : '. $this->formatter_datetime->format(time()));
            $cell_row++;
            $cell_row++;

            // write header
            $row_header_start = $row_header_end = $cell_row;
            foreach ($column_arr as $key_column => $value_column) {
                $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->header);
                $sheet->setCellValue($cell_column . $cell_row, strtoupper($value_column['title']));
                $sheet->getColumnDimension($cell_column)->setWidth(ceil(1.5 * strlen($value_column['title']) + 0.6));
                $sheet->getStyle($cell_column . $cell_row)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
    
                $cell_column++;
            }

            $cell_row++;
            $cell_column_end = $cell_column;
            $cell_column = $cell_column_start;
            
            $no = 0;
            // write content
            if (is_array($data_arr) && count($data_arr) > 0) {
                foreach ($data_arr as $key_data_arr => $value_data_arr) {
                    $cell_column = $cell_column_start;
                    
                    foreach ($column_arr as $key_column => $value_column) {
                        $width_current = $sheet->getColumnDimension($cell_column)->getWidth();
                        if ($key_column == "no") {
                            $no++;
                            $sheet->setCellValueExplicit($cell_column . $cell_row, $no, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $width_calc = ceil(1.3 * strlen($no) + 0.5);
                        } else {
                            if (isset($value_column['type']) && !empty($value_column['type'])) {
                                $sheet->setCellValueExplicit($cell_column . $cell_row, $value_data_arr[$key_column], $value_column['type']);
                            } else {
                                $sheet->setCellValue($cell_column . $cell_row, $value_data_arr[$key_column]);
                            }
                            $width_calc = ceil(1.3 * strlen($value_data_arr[$key_column]) + 0.5);
                        }
                        if ($width_current < $width_calc) {
                            if ($width_calc > 100) {
                                $width_calc = 100;
                            }
                            $sheet->getColumnDimension($cell_column)->setWidth($width_calc);
                        }

                        if (isset($value_column['format_code'])) {
                            $sheet->getStyle($cell_column . $cell_row)->getNumberFormat()->setFormatCode($value_column['format_code']);
                        }

                        $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->data);
                        $sheet->getStyle($cell_column . $cell_row)->getAlignment()->setHorizontal($value_column['align'])->setWrapText(true);
                        $cell_column++;

                        // CALLBACK FUNCTION START
                        if (isset($value_column['callback_func']) && is_callable($value_column['callback_func']) ) {
                            $value_column['callback_func']($value_data_arr);
                        }
                        // CALLBACK FUNCTION END

                        // CALCULATE START for Summary
                        if (isset($value_column['is_calculate']) && $value_column['is_calculate'] ) {
                            if (isset($this->calculate->$key_column)) {
                                $this->calculate->$key_column += $value_data_arr[$key_column];
                            } else {
                                $this->calculate->$key_column = $value_data_arr[$key_column];
                            }
                        }
                        // CALCULATE END
                    }
                    $cell_row++;
                }
            } else {
                $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->sheet_title);
                $sheet->setCellValue($cell_column . $cell_row, "Tidak ada data.");
            }
            $cell_column = $cell_column_start;

            // write summary 
            foreach ($summary_arr as $key_summary => $value_summary) {
                // jika memakai kalkulasi dari data
                if (isset($value_summary['calculate_from'])) {
                    $calculate_key = $value_summary['calculate_from'];
                    $sheet->setCellValue($cell_column . $cell_row, $this->calculate->$calculate_key);
                } else {
                    $sheet->setCellValue($cell_column . $cell_row, $value_summary['value']);
                }
                
                if (isset($value_summary['align']) && !empty($value_summary['align'])) {
                    $sheet->getStyle($cell_column . $cell_row)->getAlignment()->setHorizontal($value_summary['align'])->setWrapText(true);
                }

                if (isset($value_summary['start_merge']) && isset($value_summary['end_merge'])) {
                    $cell_merge_range = "{$value_summary['start_merge']}{$cell_row}:{$value_summary['end_merge']}{$cell_row}";
                    $sheet->mergeCells($cell_merge_range);
                    $sheet->getStyle($cell_merge_range)->applyFromArray($this->theme->summary);
                    $cell_column = $value_summary['end_merge'];
                } else {
                    $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->summary);
                }
                
                if (isset($value_summary['format_code'])) {
                    $sheet->getStyle($cell_column . $cell_row)->getNumberFormat()->setFormatCode($value_summary['format_code']);
                }

                
                $cell_column++;
            }
            $cell_row++;
        }
    }

    // export ringkasan penjualan
    public function exportChild()
    {

        if (!$this->data || !is_array($this->data)) {
            return false;
        }

        $this->spreadsheet = new Spreadsheet();

        $sheetIndex = $this->spreadsheet->getFirstSheetIndex();
        $this->spreadsheet->removeSheetByIndex($sheetIndex);

        $this->writeSpreadSheetChild();
        

        $writer = new Xlsx($this->spreadsheet);

        // $writer->save('./hello world.xlsx');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        
        // CLEANUP
        // $this->spreadsheet->disconnectWorksheets();
        // unset($this->spreadsheet);
        exit;
    }

    // write Child
    private function writeSpreadSheetChild()
    {
        foreach ($this->data as $key => $value) {
            $title = (isset($value['title']) && !empty($value['title'])) ? $value['title'] : 'Ekspor Data';
            $sheet_title = (isset($value['sheet_title']) && !empty($value['sheet_title'])) ? $value['sheet_title'] : "Sheet {$key}";
            $column_arr = (isset($value['column']) && !empty($value['column'])) ? $value['column'] : [];
            $data_arr = (isset($value['data']) && !empty($value['data'])) ? $value['data'] : [];
            $summary_arr = (isset($value['summary']) && !empty($value['summary'])) ? $value['summary'] : [];

            $column_index_count = 0; 

            $this->spreadsheet->createSheet();
            
            $this->spreadsheet->getProperties()->setCreator('SODA POS')
                ->setLastModifiedBy('SODA POS')
                ->setTitle($this->title)
                ->setSubject($this->title)
                ->setDescription("")
                ->setKeywords($this->title);
            
            $sheet = $this->spreadsheet->getSheet($this->spreadsheet->getSheetCount() - 1);
            $sheet->setTitle($sheet_title);
            
            $cell_column = $cell_column_start = $cell_column_end = 'A';
            $cell_row = $cell_row_start = 1;
            
            //write title
            $sheet->getRowDimension($cell_row)->setRowHeight(20);
            $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->sheet_title);
            $sheet->setCellValue($cell_column . $cell_row, strtoupper($title));
            $cell_row++;
            $sheet->setCellValue($cell_column . $cell_row, 'Tanggal Ekspor : '. $this->formatter_datetime->format(time()));
            $cell_row++;
            $cell_row++;

            // write header
            $row_header_start = $row_header_end = $cell_row;
            foreach ($column_arr as $key_column => $value_column) {
                $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->header);
                $sheet->setCellValue($cell_column . $cell_row, strtoupper($value_column['title']));
                $sheet->getColumnDimension($cell_column)->setWidth(ceil(1.5 * strlen($value_column['title']) + 0.6));
                $sheet->getStyle($cell_column . $cell_row)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
    
                $cell_column++;
            }

            $cell_row++;
            $cell_column_end = $cell_column;
            $cell_column = $cell_column_start;
            
            $no = 0;
            // write content
            if (is_array($data_arr) && count($data_arr) > 0) {
                foreach ($data_arr as $key_data_arr => $value_data_arr) {
                    $cell_column = $cell_column_start;
                    
                    foreach ($column_arr as $key_column => $value_column) {
                        $width_current = $sheet->getColumnDimension($cell_column)->getWidth();
                        if ($key_column == "no") {
                            if(isset($value_data_arr[$key_column]['type']) && $value_data_arr[$key_column]['type'] == "main") {
                                $no++;
                                // $sheet->setCellValue($cell_column . $cell_row, $no);
                                $sheet->setCellValueExplicit($cell_column . $cell_row, $no, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                $width_calc = ceil(1.3 * strlen($no) + 0.5);
                            }
                        } else {
                            if (isset($value_column['type']) && !empty($value_column['type'])) {
                                $sheet->setCellValueExplicit($cell_column . $cell_row, $value_data_arr[$key_column]['value'], $value_column['type']);
                            } else {
                                $sheet->setCellValue($cell_column . $cell_row, $value_data_arr[$key_column]['value']);
                            }
                            $width_calc = ceil(1.3 * strlen($value_data_arr[$key_column]['value']) + 0.5);
                        }
                        if ($width_current < $width_calc) {
                            if ($width_calc > 100) {
                                $width_calc = 100;
                            }
                            $sheet->getColumnDimension($cell_column)->setWidth($width_calc);
                        }

                        if (isset($value_column['format_code'])) {
                            $sheet->getStyle($cell_column . $cell_row)->getNumberFormat()->setFormatCode($value_column['format_code']);
                        }
                        
                        if(isset($value_data_arr[$key_column]['type']) && $value_data_arr[$key_column]['type'] == "main") { 
                            $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->data_main);
                        } else {
                            $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->data);
                        }
                        $sheet->getStyle($cell_column . $cell_row)->getAlignment()->setHorizontal($value_column['align'])->setWrapText(true);
                        $cell_column++;
                    }
                    $cell_row++;
                }
            } else {
                $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->sheet_title);
                $sheet->setCellValue($cell_column . $cell_row, "Tidak ada data.");
            }
            $cell_column = $cell_column_start;

            // write summary 
            foreach ($summary_arr as $key_summary => $value_summary) {
                $sheet->setCellValue($cell_column . $cell_row, $value_summary['value']);
                
                if (isset($value_summary['align']) && !empty($value_summary['align'])) {
                    $sheet->getStyle($cell_column . $cell_row)->getAlignment()->setHorizontal($value_summary['align'])->setWrapText(true);
                }

                if (isset($value_summary['start_merge']) && isset($value_summary['end_merge'])) {
                    $cell_merge_range = "{$value_summary['start_merge']}{$cell_row}:{$value_summary['end_merge']}{$cell_row}";
                    $sheet->mergeCells($cell_merge_range);
                    $sheet->getStyle($cell_merge_range)->applyFromArray($this->theme->summary);
                    $cell_column = $value_summary['end_merge'];
                } else {
                    $sheet->getStyle($cell_column . $cell_row)->applyFromArray($this->theme->summary);
                }
                
                if (isset($value_summary['format_code'])) {
                    $sheet->getStyle($cell_column . $cell_row)->getNumberFormat()->setFormatCode($value_summary['format_code']);
                }

                
                $cell_column++;
            }
            $cell_row++;
        }
    }
}
