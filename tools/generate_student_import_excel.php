<?php

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);

$outputPath = __DIR__.'/../storage/app/public/import_siswa_masal.xlsx';

$headers = ['nis', 'nisn', 'nama_lengkap', 'kelas', 'angkatan', 'tipe_siswa', 'aktif'];
$classes = ['X-A', 'XI-MIPA 1', 'XI-IPS 1', 'XII-MIPA 1', 'XII-IPS 1'];
$batches = ['2026/2027', '2025/2026', '2024/2025'];
$studentTypes = ['Reguler', 'Full Day', 'Asrama'];
$activeOptions = ['aktif', 'nonaktif'];

$firstNames = [
    'Ahmad', 'Siti', 'Muhammad', 'Aulia', 'Rizky', 'Laila', 'Naufal', 'Dewi', 'Farhan', 'Nabila',
    'Zahra', 'Rafi', 'Nadia', 'Fadhil', 'Salma', 'Hafiz', 'Putri', 'Dimas', 'Intan', 'Raka',
];

$lastNames = [
    'Pratama', 'Rahmawati', 'Setiawan', 'Nuraini', 'Saputra', 'Zahira', 'Maulana', 'Puspitasari',
    'Wibowo', 'Kusuma', 'Fauziah', 'Ramadhan', 'Permatasari', 'Hidayat', 'Utami', 'Hakim',
    'Azzahra', 'Wijaya', 'Maharani', 'Firmansyah',
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Import Siswa');
$sheet->fromArray($headers, null, 'A1');

for ($i = 1; $i <= 100; $i++) {
    $class = $classes[($i - 1) % count($classes)];

    $batch = match (true) {
        str_starts_with($class, 'XII') => '2024/2025',
        str_starts_with($class, 'XI') => '2025/2026',
        default => '2026/2027',
    };

    $row = $i + 1;
    $sheet->fromArray([
        (string) (2026100 + $i),
        (string) (9982000000 + $i),
        $firstNames[($i - 1) % count($firstNames)].' '.$lastNames[(int) floor(($i - 1) / count($firstNames)) % count($lastNames)],
        $class,
        $batch,
        $studentTypes[($i - 1) % count($studentTypes)],
        'aktif',
    ], null, 'A'.$row);
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00422F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];

$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:G101');

foreach (range('A', 'G') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

$referenceSheet = $spreadsheet->createSheet();
$referenceSheet->setTitle('Referensi');
$referenceSheet->fromArray(['kelas', 'angkatan', 'tipe_siswa', 'aktif'], null, 'A1');

$maxReferenceRows = max(count($classes), count($batches), count($studentTypes), count($activeOptions));

for ($i = 0; $i < $maxReferenceRows; $i++) {
    $referenceSheet->fromArray([
        $classes[$i] ?? null,
        $batches[$i] ?? null,
        $studentTypes[$i] ?? null,
        $activeOptions[$i] ?? null,
    ], null, 'A'.($i + 2));
}

$referenceSheet->getStyle('A1:D1')->applyFromArray($headerStyle);

foreach (range('A', 'D') as $column) {
    $referenceSheet->getColumnDimension($column)->setAutoSize(true);
}

$validations = [
    'D2:D101' => '"'.implode(',', $classes).'"',
    'E2:E101' => '"'.implode(',', $batches).'"',
    'F2:F101' => '"'.implode(',', $studentTypes).'"',
    'G2:G101' => '"'.implode(',', $activeOptions).'"',
];

foreach ($validations as $range => $formula) {
    [$start, $end] = explode(':', $range);
    preg_match('/^([A-Z]+)(\d+)$/', $start, $startMatches);
    preg_match('/^([A-Z]+)(\d+)$/', $end, $endMatches);

    $column = $startMatches[1];
    $startRow = (int) $startMatches[2];
    $endRow = (int) $endMatches[2];

    for ($row = $startRow; $row <= $endRow; $row++) {
        $validation = $sheet->getCell($column.$row)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Input tidak valid');
        $validation->setError('Pilih nilai dari daftar referensi.');
        $validation->setFormula1($formula);
    }
}

$spreadsheet->setActiveSheetIndex(0);

(new Xlsx($spreadsheet))->save($outputPath);

echo $outputPath.PHP_EOL;
