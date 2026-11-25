<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;

class InventoryExport
{
    /**
     * Build a small, standards-compliant PDF without requiring an external binary or extension.
     *
     * @param Collection<int, Book> $books
     */
    public function pdf(Collection $books): string
    {
        $columns = ['Title', 'Author', 'Category', 'Unit price (PHP)', 'Availability'];
        $rows = $books->map(fn (Book $book) => [
            $book->title,
            $book->author,
            $book->category,
            number_format((float) $book->unit_price, 2),
            $book->availability_status,
        ])->all();

        $lines = [implode(' | ', $columns)];
        foreach ($rows as $row) {
            $lines[] = implode(' | ', $row);
        }
        if ($rows === []) {
            $lines[] = 'No books match the selected filters.';
        }

        $pages = array_chunk($lines, 36);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pageReferences = [];

        foreach ($pages as $pageIndex => $pageLines) {
            $pageObject = 5 + ($pageIndex * 2);
            $contentObject = $pageObject + 1;
            $pageReferences[] = "{$pageObject} 0 R";
            $content = "BT\n/F1 15 Tf\n38 555 Td\n(Book Inventory Report) Tj\n/F1 9 Tf\n0 -18 Td\n";
            foreach ($pageLines as $lineIndex => $line) {
                if ($lineIndex > 0) {
                    $content .= "0 -13 Td\n";
                }
                $content .= '(' . $this->pdfText($line) . ") Tj\n";
            }
            $content .= "ET";

            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObject} 0 R >>";
            $objects[$contentObject] = '<< /Length ' . strlen($content) . " >>\nstream\n{$content}\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageReferences) . '] /Count ' . count($pageReferences) . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (max(array_keys($objects)) + 1) . "\n0000000000 65535 f \n";
        for ($number = 1; $number <= max(array_keys($objects)); $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number] ?? 0);
        }
        $pdf .= "trailer\n<< /Size " . (max(array_keys($objects)) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * @param Collection<int, Book> $books
     */
    public function xlsx(Collection $books): string
    {
        $rows = [
            ['Title', 'Author', 'Category', 'Unit price (PHP)', 'Availability'],
        ];
        foreach ($books as $book) {
            $rows[] = [$book->title, $book->author, $book->category, $book->unit_price, $book->availability_status];
        }
        if ($books->isEmpty()) {
            $rows[] = ['No books match the selected filters.', '', '', '', ''];
        }

        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $cellXml = '';
            foreach ($row as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1) . ($rowIndex + 1);
                if ($rowIndex > 0 && $columnIndex === 3 && is_numeric($value)) {
                    $cellXml .= '<c r="' . $reference . '"><v>' . $this->xmlText((string) $value) . '</v></c>';
                } else {
                    $cellXml .= '<c r="' . $reference . '" t="inlineStr"><is><t xml:space="preserve">' . $this->xmlText((string) $value) . '</t></is></c>';
                }
            }
            $sheetRows .= '<row r="' . ($rowIndex + 1) . '">' . $cellXml . '</row>';
        }

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Book Inventory" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $sheetRows . '</sheetData></worksheet>',
        ];

        return $this->zip($files);
    }

    private function pdfText(string $text): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $ascii = substr($ascii === false ? '' : $ascii, 0, 130);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }

    private function xmlText(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    /**
     * Assemble the minimal Open Packaging Convention ZIP structure using core zlib.
     *
     * @param array<string, string> $files
     */
    private function zip(array $files): string
    {
        $data = '';
        $centralDirectory = '';
        foreach ($files as $name => $contents) {
            $compressed = gzdeflate($contents);
            if ($compressed === false) {
                throw new \RuntimeException('Unable to compress the Excel export.');
            }
            $crc = crc32($contents);
            $offset = strlen($data);
            $data .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 8, 0, 33, $crc, strlen($compressed), strlen($contents), strlen($name), 0);
            $data .= $name . $compressed;
            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 8, 0, 33, $crc, strlen($compressed), strlen($contents), strlen($name), 0, 0, 0, 0, 0, $offset);
            $centralDirectory .= $name;
        }

        $centralOffset = strlen($data);
        $data .= $centralDirectory;
        $data .= pack('VvvvvVVv', 0x06054b50, 0, 0, count($files), count($files), strlen($centralDirectory), $centralOffset, 0);

        return $data;
    }
}
