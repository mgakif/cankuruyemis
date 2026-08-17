<?php

namespace App\Services\Imports;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class XlsxCatalogReader
{
    public function read(string $path): array
    {
        $workbook = $this->readWorkbook($path);

        foreach ($workbook as $rows) {
            if ($rows !== []) {
                return $rows;
            }
        }

        return [];
    }

    public function readWorkbook(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("Excel dosyasi bulunamadi veya okunamadi: {$path}");
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Excel dosyasi acilamadi veya bozuk: {$path}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $worksheets = $this->resolveWorksheetPaths($zip);
        $sheets = [];

        foreach ($worksheets as $sheetName => $worksheetPath) {
            $sheetXml = $zip->getFromName($worksheetPath);

            if (! $sheetXml) {
                $zip->close();

                throw new RuntimeException("Excel sayfasi okunamadi: {$sheetName}");
            }

            $sheet = new SimpleXMLElement($sheetXml);
            $sheet->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $rows = [];

            foreach ($sheet->xpath('//main:sheetData/main:row') as $row) {
                $current = [];

                foreach ($row->c as $cell) {
                    $reference = (string) ($cell['r'] ?? '');
                    $columnIndex = $this->columnIndexFromReference($reference);
                    $type = (string) ($cell['t'] ?? '');
                    $value = isset($cell->v) ? (string) $cell->v : '';

                    if ($type === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    }

                    $current[$columnIndex] = trim($value);
                }

                if ($current === []) {
                    continue;
                }

                ksort($current);
                $rows[] = array_values($current);
            }

            $headers = array_shift($rows) ?? [];

            $mappedRows = array_map(function (array $row) use ($headers) {
                $mapped = [];

                foreach ($headers as $index => $header) {
                    $mapped[$header] = $row[$index] ?? null;
                }

                return $mapped;
            }, $rows);

            $sheets[$sheetName] = $mappedRows;
        }

        $zip->close();

        return $sheets;
    }

    protected function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if (! $xml) {
            return [];
        }

        $shared = new SimpleXMLElement($xml);
        $shared->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];

        foreach ($shared->xpath('//main:si') as $item) {
            $item->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $texts = $item->xpath('.//main:t') ?: [];
            $strings[] = collect($texts)->map(fn ($text) => (string) $text)->implode('');
        }

        return $strings;
    }

    protected function resolveWorksheetPaths(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (! $workbookXml || ! $relsXml) {
            throw new RuntimeException('Excel workbook metadata okunamadi.');
        }

        $workbook = new SimpleXMLElement($workbookXml);
        $rels = new SimpleXMLElement($relsXml);

        $rels->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $sheets = $workbook->xpath('//main:sheets/main:sheet') ?: [];
        $relationships = [];
        $worksheetPaths = [];

        foreach ($rels->Relationship as $relationship) {
            $relationships[(string) $relationship['Id']] = 'xl/'.ltrim((string) $relationship['Target'], '/');
        }

        foreach ($sheets as $sheet) {
            $relationshipId = (string) $sheet->attributes('r', true)->id;

            if (! isset($relationships[$relationshipId])) {
                continue;
            }

            $worksheetPaths[(string) $sheet['name']] = $relationships[$relationshipId];
        }

        if ($worksheetPaths === []) {
            throw new RuntimeException('Excel worksheet relation bulunamadi.');
        }

        return $worksheetPaths;
    }

    protected function columnIndexFromReference(string $reference): int
    {
        preg_match('/([A-Z]+)/', $reference, $matches);

        $letters = $matches[1] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max($index - 1, 0);
    }
}
