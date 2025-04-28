<?php

declare(strict_types=1);

// Set security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Constants
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_TYPES = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

// Error handling
function handleError(string $message): never {
    $_SESSION['error'] = $message;
    header("Location: index.php");
    exit;
}

// Start session for messages
session_start();

// Check request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    handleError("Invalid request method");
}

// Check if files were uploaded
if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
    handleError("No files were uploaded");
}

// Load required files
$vendorPath = $_SERVER['DOCUMENT_ROOT'] . '/vendor/src/';
if (!file_exists($vendorPath . 'SimpleXLS.php') || !file_exists($vendorPath . 'SimpleXLSX.php')) {
    handleError("Required vendor files are missing");
}

require($vendorPath . 'SimpleXLS.php');
require($vendorPath . 'SimpleXLSX.php');

// Define languages
$myLanguages = [
    'en' => 'English',
    'tr' => 'Türkçe',
];

// Create language directories
foreach ($myLanguages as $key => $value) {
    if (!is_dir($key)) {
        if (!mkdir($key, 0755, true)) {
            handleError("Failed to create directory for language: $key");
        }
    }
}

// Process uploaded files
$files = count($_FILES['files']['name']);
$successCount = 0;

for ($i = 0; $i < $files; $i++) {
    $file = [
        'name' => $_FILES['files']['name'][$i],
        'type' => $_FILES['files']['type'][$i],
        'tmp_name' => $_FILES['files']['tmp_name'][$i],
        'error' => $_FILES['files']['error'][$i],
        'size' => $_FILES['files']['size'][$i]
    ];

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        handleError("Error uploading file: " . $file['name']);
    }

    if (!in_array($file['type'], ALLOWED_TYPES, true)) {
        handleError("Invalid file type: " . $file['name']);
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        handleError("File too large: " . $file['name']);
    }

    try {
        fileManipulate($file);
        $successCount++;
    } catch (Exception $e) {
        handleError("Error processing file " . $file['name'] . ": " . $e->getMessage());
    }
}

// Set success message
$_SESSION['success'] = "Successfully processed $successCount file(s)";

// Redirect back to index
header("Location: index.php");
exit;

function fileManipulate(array $file): void {
    [$fileName, $destPath] = uploadFile($file);
    $rows = excelConverter($destPath, $file['type']);
    $transposeArray = arrayTranspose($rows);
    spliteArrayByLang($transposeArray, $fileName);
    unlink($destPath);
}

function arrayTranspose(array $rows): array {
    $output = [];

    foreach ($GLOBALS['myLanguages'] as $language) {
        $output[$language] = [];
    }

    foreach ($rows as $row) {
        foreach ($GLOBALS['myLanguages'] as $language) {
            $output[$language] += [
                $row[''] => $row[$language],
            ];
        }
    }
    return $output;
}

function spliteArrayByLang(array $rows, string $fileName): void {
    foreach ($GLOBALS['myLanguages'] as $key => $value) {
        $array = arrayUndot($rows[$value]);
        saveFile($key, $array, $fileName);
    }
}

function uploadFile(array $file): array {
    $fileTmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
    $uploadFileDir = './';
    $destPath = $uploadFileDir . $newFileName;

    move_uploaded_file($fileTmpPath, $destPath);

    return [$fileNameCmps[0], $destPath, $fileExtension];
}

function excelConverter(string $destPath, string $fileType): array {
    $xlsx = match($fileType) {
        'application/vnd.ms-excel' => SimpleXLS::parse($destPath),
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => SimpleXLSX::parse($destPath),
        default => throw new Exception("Unsupported file type: $fileType")
    };

    $headerValues = $rows = [];

    foreach ($xlsx->rows() as $k => $r) {
        if ($k === 0) {
            $headerValues = $r;
            continue;
        }
        $rows[] = array_combine($headerValues, $r);
    }

    return $rows;
}

function saveFile(string $key, array $array, string $fileName): void {
    if (isset($_POST['submitJson'])) {
        $trim = trim((json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), '[]');
        file_put_contents("$key/$fileName.json", $trim);
    } else {
        file_put_contents("$key/$fileName.php", "<?php \n return " . var_export($array, true) . ";");
    }
}

function arrayUndot(array $dottedArray): array {
    $array = [];

    foreach ($dottedArray as $key => $value) {
        arraySet($array, $key, $value);
    }

    return $array;
}

function arraySet(array &$array, ?string $key, mixed $value): array {
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);

    foreach ($keys as $i => $key) {
        if (count($keys) === 1) {
            break;
        }

        unset($keys[$i]);

        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }

        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
}