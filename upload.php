<?php
header("Location: index.php");
require_once 'vendor\src\SimpleXLS.php';
require_once 'vendor\src\SimpleXLSX.php';

$my_languages = [
    'en' => 'English',
    'tr' => 'Türkçe',
];

foreach ($my_languages as $key => $value) {
    if (!is_dir($key)) {
        mkdir($key);
    }
}

$countfiles = count($_FILES['uploadedFile']['name']);

for ($i = 0; $i < $countfiles; $i++) {

    $fileType = $_FILES['uploadedFile']['type'][$i];

    $filename = $_FILES['uploadedFile']['name'][$i];

    $tmp_name = $_FILES['uploadedFile']['tmp_name'][$i];

    if ($fileType != "application/vnd.ms-excel") {
        echo "Only xls, xlsx files are allowed.";
        continue;
    }

    get_file([
        'tmp_name' => $tmp_name,
        'name' => $filename,
        'type' => $fileType,
    ]);
}

function get_file($file)
{
    list($fileName, $dest_path) = upload_file($file);

    $rows = Xls_converter($dest_path);

    $TransposeArray = array_transpose($rows);

    splite_array_by_lang($TransposeArray, $fileName);

    unlink($dest_path);
}

function array_transpose(array $rows)
{
    $output = [];

    foreach ($GLOBALS['my_languages'] as $l) {
        $output[$l] = [];
    }

    foreach ($rows as $d) {
        foreach ($GLOBALS['my_languages'] as $la) {
            $output[$la] += [
                $d[''] => $d[$la],
            ];
        }
    }
    return $output;
}

function splite_array_by_lang(array $rows, $fileName)
{
    foreach ($GLOBALS['my_languages'] as $key => $value) {
        $array = array_undot($rows[$value]);

        Save_file($key, $array, $fileName);
    }
}

function upload_file($file)
{
    $fileTmpPath = $file['tmp_name'];

    $fileName = $file['name'];

    $fileNameCmps = explode(".", $fileName);

    $fileExtension = strtolower(end($fileNameCmps));

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    $uploadFileDir = './';

    $dest_path = $uploadFileDir . $newFileName;

    move_uploaded_file($fileTmpPath, $dest_path);

    return array($fileNameCmps[0], $dest_path, $fileExtension);
}

function Xls_converter($dest_path)
{
    $xlsx = SimpleXLS::parse("$dest_path");

    $header_values = $rows = [];

    foreach ($xlsx->rows() as $k => $r) {
        if ($k === 0) {
            $header_values = $r;
            continue;
        }
        $rows[] = array_combine($header_values, $r);
    }

    return $rows;
}

function save_file($key, $array, $file_name)
{
    if ($_POST['uploadBtn'] == 'Json') {
        $trim = trim((json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), '[]');

        file_put_contents("$key/$file_name.json", $trim);
    } else {
        file_put_contents("$key/$file_name.php", "<?php \n return " . var_export($array, true) . ";");
    }
}

function array_undot($dottedArray)
{

    $array = array();

    foreach ($dottedArray as $key => $value) {
        array_set($array, $key, $value);
    }

    return $array;
}

function array_set(array &$array, ?string $key, $value)
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
