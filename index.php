<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Converter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css" />
    <style>
        body {
            text-align: center;
        }

        h1 {
            color: rgb(117, 59, 17);
        }

        input[type="file"] {
            padding: 10px 20px;
            border: 2px solid rgb(117, 59, 17);
            border-radius: 10px;
            cursor: pointer;
        }

        input[type="submit"] {
            background-color: rgb(201, 115, 54);
            padding: 10px 50px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
    </style>
    <script>
        function validateUpload() {
            var x = document.forms["upload"]["uploadedFile[]"].value;
            if (x == "" || x == null) {
                alert("Please choose a file or multiple files to upload");
                return false;
            }
        }

        function clearInput() {
            document.getElementById("uploadedFile").value = "";
        }
    </script>
</head>

<body>
    <center>
        <form name="upload" method="POST" action="upload.php" enctype="multipart/form-data" onsubmit="return validateUpload()" required>
            <div>
                <h1>Upload XLS/XLSX File:</h1><br>
                <input type="file" name="uploadedFile[]" accept=".xlsx, .xls" multiple>
            </div><br>
            <input type="submit" name="uploadBtn" value="Json">
            <input type="submit" name="uploadBtn" value="Array">

            <button>Clear</button>
        </form>
    </center>
</body>

</html>