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

for($i=0;$i<$countfiles;$i++){

    $filename = $_FILES['uploadedFile']['name'][$i];

    $tmp_name = $_FILES['uploadedFile']['tmp_name'][$i];

    get_file([
        'tmp_name' => $tmp_name,
        'name' => $filename
    ]);
}

function get_file($file): void
{
    list($fileName, $dest_path) = upload_file($file);

    $rows = Xls_converter($dest_path);

    filtered_by_lang($rows, $fileName);

}

function filtered_by_lang(array $rows, $fileName): void
{
    foreach ($GLOBALS['my_languages'] as $key => $value) {
        $filtered = multi_array_search_with_condition(
            $rows,
            array('' => $value)
        );

        $array = array_undot($filtered[0]);

        Save_file($key, $array, $fileName);
    }
}

function upload_file($file): array
{
    $fileTmpPath = $file['tmp_name'];

    $fileName = $file['name'];

    $fileNameCmps = explode(".", $fileName);

    $uploadFileDir = './uploaded_files/';

    $dest_path = $uploadFileDir . $fileName;

    move_uploaded_file($fileTmpPath, $dest_path);

    return array($fileNameCmps[0], $dest_path);

}

function Xls_converter($dest_path): array
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

function multi_array_search_with_condition($array, $condition): array
{
    $foundItems = array();

    foreach ($array as $item) {
        $find = true;
        foreach ($condition as $key => $value) {
            if (isset($item[$key]) && $item[$key] == $value) {
                $find = true;
            } else {
                $find = false;
            }
        }
        if ($find) {
            array_push($foundItems, $item);
        }
    }
    return $foundItems;
}

function array_undot($dottedArray)
{

    $array = array();

    foreach ($dottedArray as $key => $value) {
        array_set($array, $key, $value);
    }

    return $array;
}

function array_set(array &$array, ?string $key, $value): array
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