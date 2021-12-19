<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converter</title>
</head>
<body style="text-align: center;">
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <div>
            <h1 style="color:rgb(117, 59, 17);">Upload XLS/XLSX File : </h1><br>
            <input type="file" name="uploadedFile[]" accept=".xls, .xlsx/*" multiple="multiple" style="padding: 10px 20px; border: 2px solid rgb(117, 59, 17); border-radius: 10px;cursor: pointer"/>
        </div><br>
        <input type="submit" name="uploadBtn" value="Json" style="background-color: rgb(201, 115, 54); padding: 10px 50px; border: none; border-radius: 10px;cursor: pointer" />
        <input type="submit" name="uploadBtn" value="Array" style="background-color: rgb(201, 115, 54); padding: 10px 50px; border: none; border-radius: 10px;cursor: pointer;"/>
    </form>
</body>
</html>
