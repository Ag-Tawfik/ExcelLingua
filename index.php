<?php

declare(strict_types=1);

session_start();

// Clear messages after displaying them
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Define constants for JavaScript
const JS_MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const JS_ALLOWED_TYPES = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExcelLingua - Excel Translation Converter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef3f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
                        secondary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .file-input-container {
            border: 2px dashed #f97316;
            transition: all 0.3s ease;
        }
        .file-input-container:hover {
            border-color: #ea580c;
            background-color: #fff7ed;
        }
        .file-input-container.dragover {
            border-color: #dc2626;
            background-color: #fef3f2;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">ExcelLingua</h1>
            <p class="text-gray-600">Excel Translation Converter</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form name="upload" method="POST" action="upload.php" enctype="multipart/form-data" onsubmit="return validateUpload()" class="space-y-6">
                <div class="file-input-container rounded-lg p-8 text-center cursor-pointer" id="dropZone">
                    <input type="file" id="files" name="files[]" accept=".xlsx, .xls" multiple class="hidden" onchange="updateFileInfo()">
                    <div class="space-y-2">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-secondary-600 hover:text-secondary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-secondary-500">
                                <span>Upload Excel files</span>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">XLS, XLSX up to 5MB</p>
                    </div>
                    <div id="fileInfo" class="mt-4 text-sm text-gray-600"></div>
                </div>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <button type="submit" name="submitJson" class="px-6 py-3 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 transition-colors">
                        Convert to JSON
                    </button>
                    <button type="submit" name="submitArray" class="px-6 py-3 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 transition-colors">
                        Convert to PHP Array
                    </button>
                    <button type="reset" onclick="document.getElementById('fileInfo').textContent = ''; document.getElementById('message').style.display = 'none';" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Clear
                    </button>
                </div>
            </form>
        </div>

        <div id="message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"></div>
    </div>

    <script>
        const MAX_FILE_SIZE = <?= JS_MAX_FILE_SIZE ?>;
        const ALLOWED_TYPES = <?= json_encode(JS_ALLOWED_TYPES) ?>;
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('files');

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('dragover');
        }

        function unhighlight(e) {
            dropZone.classList.remove('dragover');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            updateFileInfo();
        }

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
            messageDiv.className = `bg-${type === 'error' ? 'red' : 'green'}-100 border border-${type === 'error' ? 'red' : 'green'}-400 text-${type === 'error' ? 'red' : 'green'}-700 px-4 py-3 rounded relative mb-4`;
            messageDiv.style.display = "block";
        }

        function updateFileInfo() {
            const files = document.getElementById("files").files;
            const fileInfo = document.getElementById("fileInfo");
            
            if (files.length > 0) {
                let info = `<div class="space-y-2">`;
                info += `<p class="font-medium">Selected ${files.length} file(s):</p>`;
                info += `<ul class="list-disc list-inside text-left">`;
                for (const file of files) {
                    info += `<li>${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</li>`;
                }
                info += `</ul></div>`;
                fileInfo.innerHTML = info;
            } else {
                fileInfo.innerHTML = "";
            }
        }
    </script>
</body>

</html>