<?php
/**
 * Simple Excel Helper - Alternative to PhpSpreadsheet for PHP 8.1 compatibility
 */
class SimpleExcelHelper {
    
    /**
     * Export data to CSV format
     */
    public static function exportToCSV($data, $filename = 'export.csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export data to XLS format (simple HTML table)
     */
    public static function exportToXLS($data, $filename = 'export.xls') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo '<table border="1">';
        
        // Write headers
        if (!empty($data)) {
            echo '<tr>';
            foreach (array_keys($data[0]) as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr>';
        }
        
        // Write data
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
    
    /**
     * Read CSV file
     */
    public static function readCSV($filepath) {
        $data = [];
        
        if (($handle = fopen($filepath, "r")) !== FALSE) {
            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            
            // Read headers
            $headers = fgetcsv($handle);
            
            // Read data
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        }
        
        return $data;
    }
    
    /**
     * Read Excel file (XLS/XLSX) - Fallback to CSV if possible
     */
    public static function readExcel($filepath) {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        if ($extension === 'csv') {
            return self::readCSV($filepath);
        }
        
        // For XLS/XLSX, try to use PhpSpreadsheet if available
        if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
                $worksheet = $spreadsheet->getActiveSheet();
                $data = [];
                
                $headers = [];
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                // Read headers
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $headers[] = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
                }
                
                // Read data
                $highestRow = $worksheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $rowData[] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
                    if (count($rowData) === count($headers)) {
                        $data[] = array_combine($headers, $rowData);
                    }
                }
                
                return $data;
            } catch (Exception $e) {
                // Fallback to error message
                throw new Exception("Error reading Excel file: " . $e->getMessage());
            }
        }
        
        throw new Exception("PhpSpreadsheet not available. Please use CSV format instead.");
    }
}
?> 