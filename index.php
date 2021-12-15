<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converter</title>
</head>
<body>
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <div>
            <span>Upload XLS/XLSX File:</span>
            <input type="file" name="uploadedFile[]" accept=".xls, .xlsx/*" multiple="multiple"/>
        </div>
        <input type="submit" name="uploadBtn" value="Json" />
        <input type="submit" name="uploadBtn" value="Array" />
    </form>
</body>
</html>