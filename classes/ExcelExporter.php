<?php
/**
 * Simple Excel (XLSX) Exporter
 * Creates basic XLSX files without external dependencies
 */

class ExcelExporter {
    private $rows = [];
    private $sheetName = 'Sheet1';
    
    public function addRow($data, $isHeader = false) {
        $this->rows[] = [
            'data' => $data,
            'isHeader' => $isHeader
        ];
    }
    
    public function setSheetName($name) {
        $this->sheetName = $name;
    }
    
    public function output() {
        // Create temporary directory
        $tempDir = sys_get_temp_dir() . '/xlsx_' . uniqid();
        mkdir($tempDir);
        mkdir($tempDir . '/_rels');
        mkdir($tempDir . '/docProps');
        mkdir($tempDir . '/xl');
        mkdir($tempDir . '/xl/_rels');
        mkdir($tempDir . '/xl/worksheets');
        
        // Create [Content_Types].xml
        file_put_contents($tempDir . '/[Content_Types].xml', $this->getContentTypes());
        
        // Create _rels/.rels
        file_put_contents($tempDir . '/_rels/.rels', $this->getRels());
        
        // Create docProps/app.xml
        file_put_contents($tempDir . '/docProps/app.xml', $this->getAppXml());
        
        // Create docProps/core.xml
        file_put_contents($tempDir . '/docProps/core.xml', $this->getCoreXml());
        
        // Create xl/_rels/workbook.xml.rels
        file_put_contents($tempDir . '/xl/_rels/workbook.xml.rels', $this->getWorkbookRels());
        
        // Create xl/workbook.xml
        file_put_contents($tempDir . '/xl/workbook.xml', $this->getWorkbook());
        
        // Create xl/styles.xml
        file_put_contents($tempDir . '/xl/styles.xml', $this->getStyles());
        
        // Create xl/worksheets/sheet1.xml
        file_put_contents($tempDir . '/xl/worksheets/sheet1.xml', $this->getSheet());
        
        // Create ZIP archive
        $zip = new ZipArchive();
        $zipFile = $tempDir . '.xlsx';
        
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $this->addDirToZip($tempDir, $zip, strlen($tempDir) + 1);
            $zip->close();
            
            // Output file
            readfile($zipFile);
            
            // Cleanup
            $this->deleteDir($tempDir);
            unlink($zipFile);
        }
    }
    
    private function getContentTypes() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>';
    }
    
    private function getRels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
    }
    
    private function getAppXml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
    <Application>FERN System</Application>
</Properties>';
    }
    
    private function getCoreXml() {
        $date = date('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dc:creator>FERN System</dc:creator>
    <dcterms:created xsi:type="dcterms:W3CDTF">' . $date . '</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">' . $date . '</dcterms:modified>
</cp:coreProperties>';
    }
    
    private function getWorkbookRels() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }
    
    private function getWorkbook() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="' . htmlspecialchars($this->sheetName) . '" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
    }
    
    private function getStyles() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><name val="Calibri"/></font>
    </fonts>
    <fills count="1">
        <fill><patternFill patternType="none"/></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="0"/>
    </cellXfs>
</styleSheet>';
    }
    
    private function getSheet() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>';
        
        $rowNum = 1;
        foreach ($this->rows as $row) {
            $xml .= '<row r="' . $rowNum . '">';
            $colNum = 0;
            foreach ($row['data'] as $cell) {
                $colLetter = $this->getColumnLetter($colNum);
                $cellRef = $colLetter . $rowNum;
                $styleId = $row['isHeader'] ? '1' : '0';
                
                // Escape XML special characters
                $cellValue = htmlspecialchars($cell ?? '', ENT_XML1, 'UTF-8');
                
                $xml .= '<c r="' . $cellRef . '" s="' . $styleId . '" t="inlineStr">';
                $xml .= '<is><t>' . $cellValue . '</t></is>';
                $xml .= '</c>';
                $colNum++;
            }
            $xml .= '</row>';
            $rowNum++;
        }
        
        $xml .= '</sheetData>
</worksheet>';
        
        return $xml;
    }
    
    private function getColumnLetter($index) {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }
    
    private function addDirToZip($dir, $zip, $baseLen) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $dir . '/' . $file;
            $localPath = substr($filePath, $baseLen);
            
            if (is_dir($filePath)) {
                $this->addDirToZip($filePath, $zip, $baseLen);
            } else {
                $zip->addFile($filePath, $localPath);
            }
        }
    }
    
    private function deleteDir($dir) {
        if (!is_dir($dir)) return;
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->deleteDir($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($dir);
    }
}
