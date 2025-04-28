<?php

declare(strict_types=1);

session_start();

// Clear messages after displaying them
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExcelLingua - Excel Translation Converter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/dark.css" />
    <style>
        body {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: rgb(117, 59, 17);
            margin-bottom: 30px;
        }

        .file-input-container {
            margin: 20px 0;
            padding: 20px;
            border: 2px dashed rgb(117, 59, 17);
            border-radius: 10px;
        }

        input[type="file"] {
            padding: 10px 20px;
            border: 2px solid rgb(117, 59, 17);
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            max-width: 400px;
        }

        input[type="submit"] {
            background-color: rgb(201, 115, 54);
            padding: 10px 50px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin: 10px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: rgb(180, 100, 40);
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            display: none;
        }

        .error {
            background-color: #ff4444;
            color: white;
        }

        .success {
            background-color: #4CAF50;
            color: white;
        }

        .file-info {
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
    <script>
        const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
        const ALLOWED_TYPES = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        function validateUpload() {
            const files = document.getElementById("files").files;
            const messageDiv = document.getElementById("message");
            
            if (files.length === 0) {
                showMessage("Please choose a file or multiple files to upload", "error");
                return false;
            }

            for (const file of files) {
                if (!ALLOWED_TYPES.includes(file.type)) {
                    showMessage(`Invalid file type: ${file.name}. Please upload only Excel files (.xls or .xlsx)`, "error");
                    return false;
                }

                if (file.size > MAX_FILE_SIZE) {
                    showMessage(`File too large: ${file.name}. Maximum file size is 5MB`, "error");
                    return false;
                }
            }

            return true;
        }

        function showMessage(message, type) {
            const messageDiv = document.getElementById("message");
            messageDiv.textContent = message;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = "block";
        }

        function updateFileInfo() {
            const files = document.getElementById("files").files;
            const fileInfo = document.getElementById("fileInfo");
            
            if (files.length > 0) {
                let info = `Selected ${files.length} file(s):\n`;
                for (const file of files) {
                    info += `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)\n`;
                }
                fileInfo.textContent = info;
            } else {
                fileInfo.textContent = "";
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>ExcelLingua - Excel Translation Converter</h1>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div id="message" class="message"></div>
        
        <form name="upload" method="POST" action="upload.php" enctype="multipart/form-data" onsubmit="return validateUpload()">
            <div class="file-input-container">
                <input type="file" id="files" name="files[]" accept=".xlsx, .xls" multiple onchange="updateFileInfo()">
                <div id="fileInfo" class="file-info"></div>
            </div>
            
            <div>
                <input type="submit" name="submitJson" value="Convert to JSON">
                <input type="submit" name="submitArray" value="Convert to PHP Array">
                <input type="reset" value="Clear" onclick="document.getElementById('fileInfo').textContent = ''; document.getElementById('message').style.display = 'none';">
            </div>
        </form>
    </div>
</body>

</html>