<?php

require($_SERVER['DOCUMENT_ROOT'] . '/vendor/src/SimpleXLS.php');
require($_SERVER['DOCUMENT_ROOT'] . '/vendor/src/SimpleXLSX.php');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
}

$myLanguages = [
    'en' => 'English',
    'tr' => 'Türkçe',
];

foreach ($myLanguages as $key => $value) {
    if (!is_dir($key)) {
        mkdir($key);
    }
}

$files = count($_FILES['files']['name']);

for ($i = 0; $i < $files; $i++) {

    $fileType = $_FILES['files']['type'][$i];

    $fileName = $_FILES['files']['name'][$i];

    $tmpName = $_FILES['files']['tmp_name'][$i];

    if ($fileType != "application/vnd.ms-excel" && $fileType != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
        return false;
    }

    fileManipulate([
        'tmp_name' => $tmpName,
        'name' => $fileName,
        'type' => $fileType,
    ]);
}

function fileManipulate($file)
{
    [$fileName, $destPath] = uploadFile($file);

    $rows = excelConverter($destPath, $file['type']);

    $transposeArray = arrayTranspose($rows);

    spliteArrayByLang($transposeArray, $fileName);

    unlink($destPath);
}

function arrayTranspose(array $rows)
{
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

function spliteArrayByLang(array $rows, $fileName)
{
    foreach ($GLOBALS['myLanguages'] as $key => $value) {
        $array = arrayUndot($rows[$value]);

        saveFile($key, $array, $fileName);
    }
}

function uploadFile($file)
{
    $fileTmpPath = $file['tmp_name'];

    $fileName = $file['name'];

    $fileNameCmps = explode(".", $fileName);

    $fileExtension = strtolower(end($fileNameCmps));

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    $uploadFileDir = './';

    $destPath = $uploadFileDir . $newFileName;

    move_uploaded_file($fileTmpPath, $destPath);

    return array($fileNameCmps[0], $destPath, $fileExtension);
}

function excelConverter($destPath, $fileType)
{
    if ($fileType == 'application/vnd.ms-excel') {
        $xlsx = SimpleXLS::parse("$destPath");
    } else {
        $xlsx = SimpleXLSX::parse("$destPath");
    }

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

function saveFile(string $key, array $array, string $fileName)
{
    if (isset($_POST['submitJson'])) {
        $trim = trim((json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), '[]');
        file_put_contents("$key/$fileName.json", $trim);
    } else {
        file_put_contents("$key/$fileName.php", "<?php \n return " . var_export($array, true) . ";");
    }
}

function arrayUndot($dottedArray)
{

    $array = array();

    foreach ($dottedArray as $key => $value) {
        arraySet($array, $key, $value);
    }

    return $array;
}

function arraySet(array &$array, ?string $key, $value)
{
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

header("Location: index.php");