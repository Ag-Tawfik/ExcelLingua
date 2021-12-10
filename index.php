<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converter</title>
</head>
<body>
    <form method="POST" action="General.php" enctype="multipart/form-data">
        <div>
            <span>Upload XLS/XLSX File:</span>
            <input type="file" name="uploadedFile[]" accept="xls,xlsx/*" multiple="multiple"/>
        </div>
        <input type="submit" name="uploadBtn" value="Json" />
        <input type="submit" name="uploadBtn" value="Array" />
    </form>

    <!-- <form action="General.php" method="post" enctype="multipart/form-data" >
            <label for="Array" >Convert To Array</label>
            <input type="radio" name="uploadBtn" id="Array" value="array" />
            <label for="Json" >Convert To Json</label>
            <input type="radio" name="uploadBtn" id="Json" value="json" />
        </form> -->
</body>
</html>
