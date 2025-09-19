<?php

namespace App;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelHelper
{
    /**
     * Create and download Excel file (.xls format for legacy compatibility)
     */
    public static function createAndDownloadExcel($data, $headers, $filename, $title = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        
        // Add title if provided
        if ($title) {
            $sheet->mergeCells('A1:' . self::getColumnLetter(count($headers)) . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $currentRow = 2;
        }
        
        // Add headers
        $headerRow = $currentRow;
        foreach ($headers as $colIndex => $header) {
            $columnLetter = self::getColumnLetter($colIndex + 1);
            $sheet->setCellValue($columnLetter . $headerRow, $header);
            $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Add data
        $dataRow = $headerRow + 1;
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $columnLetter = self::getColumnLetter($colIndex + 1);
                $sheet->setCellValue($columnLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        
        // Auto-size columns
        foreach (range('A', self::getColumnLetter(count($headers))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders
        $lastRow = $dataRow - 1;
        $lastColumn = self::getColumnLetter(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Set headers for download (XLS)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Create XLS writer (legacy Excel format)
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Create Excel file and save to server (.xls format)
     */
    public static function createExcelFile($data, $headers, $filename, $folder, $title = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        
        // Add title if provided
        if ($title) {
            $sheet->mergeCells('A1:' . self::getColumnLetter(count($headers)) . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $currentRow = 2;
        }
        
        // Add headers
        $headerRow = $currentRow;
        foreach ($headers as $colIndex => $header) {
            $columnLetter = self::getColumnLetter($colIndex + 1);
            $sheet->setCellValue($columnLetter . $headerRow, $header);
            $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Add data
        $dataRow = $headerRow + 1;
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $columnLetter = self::getColumnLetter($colIndex + 1);
                $sheet->setCellValue($columnLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        
        // Auto-size columns
        foreach (range('A', self::getColumnLetter(count($headers))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders
        $lastRow = $dataRow - 1;
        $lastColumn = self::getColumnLetter(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Create directory if not exists
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        
        // Save file as .xls
        $filepath = $folder . '/' . $filename . '.xls';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Create Excel file and save to server (.xlsx format)
     */
    public static function createExcelFileXlsx($data, $headers, $filename, $folder, $title = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        
        // Add title if provided
        if ($title) {
            $sheet->mergeCells('A1:' . self::getColumnLetter(count($headers)) . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $currentRow = 2;
        }
        
        // Add headers
        $headerRow = $currentRow;
        foreach ($headers as $colIndex => $header) {
            $columnLetter = self::getColumnLetter($colIndex + 1);
            $sheet->setCellValue($columnLetter . $headerRow, $header);
            $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Add data
        $dataRow = $headerRow + 1;
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $columnLetter = self::getColumnLetter($colIndex + 1);
                $sheet->setCellValue($columnLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        
        // Auto-size columns
        foreach (range('A', self::getColumnLetter(count($headers))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders
        $lastRow = $dataRow - 1;
        $lastColumn = self::getColumnLetter(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Create directory if not exists
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        
        // Save file as .xlsx
        $filepath = $folder . '/' . $filename . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Create and download Excel file (.xlsx format for admin)
     */
    public static function createAndDownloadExcelXlsx($data, $headers, $filename, $title = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        
        // Add title if provided
        if ($title) {
            $sheet->mergeCells('A1:' . self::getColumnLetter(count($headers)) . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $currentRow = 2;
        }
        
        // Add headers
        $headerRow = $currentRow;
        foreach ($headers as $colIndex => $header) {
            $columnLetter = self::getColumnLetter($colIndex + 1);
            $sheet->setCellValue($columnLetter . $headerRow, $header);
            $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Add data
        $dataRow = $headerRow + 1;
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $columnLetter = self::getColumnLetter($colIndex + 1);
                $sheet->setCellValue($columnLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        
        // Auto-size columns
        foreach (range('A', self::getColumnLetter(count($headers))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders
        $lastRow = $dataRow - 1;
        $lastColumn = self::getColumnLetter(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Set headers for download (XLSX)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Create XLSX writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Universal method to read file as CSV (works with Excel files saved as CSV)
     */
    private static function readFileAsCSV($filepath)
    {
        $data = [];
        
        // Try to detect file encoding
        $content = file_get_contents($filepath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252']);
        
        // Try different delimiters
        $delimiters = [',', ';', "\t"];
        
        foreach ($delimiters as $delimiter) {
            $data = [];
            
            if (($handle = fopen($filepath, "r")) !== FALSE) {
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // Convert encoding if needed
                    if ($encoding !== 'UTF-8') {
                        $row = array_map(function($cell) use ($encoding) {
                            return mb_convert_encoding($cell, 'UTF-8', $encoding);
                        }, $row);
                    }
                    
                    // Clean up data
                    $row = array_map(function($cell) {
                        return trim($cell);
                    }, $row);
                    
                    $data[] = $row;
                }
                fclose($handle);
                
                // If we got data, break
                if (!empty($data)) {
                    break;
                }
            }
        }
        
        if (empty($data)) {
            throw new \Exception('Tidak dapat membaca file. Pastikan file berformat Excel (.xlsx/.xls) atau CSV (.csv) yang valid.');
        }
        
        return $data;
    }
    
    /**
     * Create template Excel file
     */
    public static function createTemplate($headers, $sampleData, $filename, $title = null, $instructions = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        
        // Add title if provided
        if ($title) {
            $sheet->mergeCells('A1:' . self::getColumnLetter(count($headers)) . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $currentRow = 2;
        }
        
        // Add instructions if provided
        if ($instructions) {
            $sheet->mergeCells('A' . $currentRow . ':' . self::getColumnLetter(count($headers)) . $currentRow);
            $sheet->setCellValue('A' . $currentRow, $instructions);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');
            $currentRow++;
        }
        
        // Add headers
        $headerRow = $currentRow;
        foreach ($headers as $colIndex => $header) {
            $columnLetter = self::getColumnLetter($colIndex + 1);
            $sheet->setCellValue($columnLetter . $headerRow, $header);
            $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        // Add sample data
        $dataRow = $headerRow + 1;
        foreach ($sampleData as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $columnLetter = self::getColumnLetter($colIndex + 1);
                $sheet->setCellValue($columnLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        
        // Auto-size columns
        foreach (range('A', self::getColumnLetter(count($headers))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders
        $lastRow = $dataRow - 1;
        $lastColumn = self::getColumnLetter(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Create XLSX writer
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Convert column index to letter (A, B, C, etc.)
     */
    private static function getColumnLetter($columnIndex)
    {
        $letter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)) . $letter;
            $columnIndex = intval($columnIndex / 26);
        }
        return $letter;
    }
    
    /**
     * Universal method to read any file format as CSV
     * This method handles .xlsx, .xls, .csv, and spreadsheet files
     */
    public static function readUploadedFile($filepath, $fileExtension)
    {
        try {
            // For all file types, use universal CSV method
            return self::readFileAsUniversalCSV($filepath);
            
        } catch (\Exception $e) {
            throw new \Exception('Error membaca file: ' . $e->getMessage());
        }
    }
    
    /**
     * Universal CSV reader that works with all file formats
     */
    private static function readFileAsUniversalCSV($filepath)
    {
        $data = [];
        
        // Try to detect file encoding
        $content = file_get_contents($filepath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252']);
        
        // Try different delimiters and approaches
        $delimiters = [',', ';', "\t"];
        $success = false;
        
        foreach ($delimiters as $delimiter) {
            $data = [];
            
            if (($handle = fopen($filepath, "r")) !== FALSE) {
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // Convert encoding if needed
                    if ($encoding !== 'UTF-8') {
                        $row = array_map(function($cell) use ($encoding) {
                            return mb_convert_encoding($cell, 'UTF-8', $encoding);
                        }, $row);
                    }
                    
                    // Clean up data
                    $row = array_map(function($cell) {
                        return trim($cell);
                    }, $row);
                    
                    $data[] = $row;
                }
                fclose($handle);
                
                // If we got data, break
                if (!empty($data)) {
                    $success = true;
                    break;
                }
            }
        }
        
        // If CSV approach failed, try alternative approach for Excel files
        if (!$success) {
            $data = self::readExcelAsText($filepath);
        }
        
        if (empty($data)) {
            throw new \Exception('Tidak dapat membaca file. Pastikan file berformat Excel (.xlsx/.xls) atau CSV (.csv) yang valid.');
        }
        
        return $data;
    }
    
    /**
     * Alternative method to read Excel files as text
     */
    private static function readExcelAsText($filepath)
    {
        $data = [];
        
        // Try to read file content as text
        $content = file_get_contents($filepath);
        
        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        // Split by lines
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Try to split by common delimiters
            $row = [];
            
            // Try tab first (common in Excel exports)
            if (strpos($line, "\t") !== false) {
                $row = explode("\t", $line);
            }
            // Try semicolon
            elseif (strpos($line, ";") !== false) {
                $row = explode(";", $line);
            }
            // Try comma
            elseif (strpos($line, ",") !== false) {
                $row = explode(",", $line);
            }
            else {
                // Single column
                $row = [$line];
            }
            
            // Clean up data
            $row = array_map(function($cell) {
                return trim($cell, '" \t\n\r\0\x0B');
            }, $row);
            
            // Skip empty rows
            if (!empty(array_filter($row))) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Create and download file in xls, xlsx, or csv format
     */
    public static function createAndDownloadFile($data, $headers, $filename, $title = null, $format = 'xls')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $currentRow = 1;
        // Add title if provided
        if ($title) {
            $sheet->mergeCells('A1:' . self::getColumnLetter(count($headers)) . '1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $currentRow = 2;
        }
        // Add headers
        $headerRow = $currentRow;
        foreach ($headers as $colIndex => $header) {
            $columnLetter = self::getColumnLetter($colIndex + 1);
            $sheet->setCellValue($columnLetter . $headerRow, $header);
            $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        // Add data
        $dataRow = $headerRow + 1;
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $columnLetter = self::getColumnLetter($colIndex + 1);
                $sheet->setCellValue($columnLetter . $dataRow, $value);
            }
            $dataRow++;
        }
        // Auto-size columns
        foreach (range('A', self::getColumnLetter(count($headers))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        // Add borders
        $lastRow = $dataRow - 1;
        $lastColumn = self::getColumnLetter(count($headers));
        $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // Output file
        $format = strtolower($format);
        if ($format === 'xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        } elseif ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
        } else { // default xls
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        }
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 