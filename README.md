# ExcelLingua - Excel Translation Converter

ExcelLingua is a powerful web application that converts Excel files (XLS/XLSX) containing translations into structured language files. It supports multiple languages and output formats, making it ideal for managing translation files in software development projects.

## Features

- **Multi-file Upload**: Upload multiple Excel files simultaneously
- **Language Support**: Currently supports English (en) and Turkish (tr) translations
- **Output Formats**: Convert to either JSON or PHP array format
- **Structured Output**: Creates organized language-specific directories
- **User-Friendly Interface**: Clean, modern UI with real-time file information
- **Security Features**: 
  - File type validation
  - Size limits (5MB per file)
  - XSS protection
  - Secure file handling

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- SimpleXLS and SimpleXLSX libraries (included in vendor directory)

## Installation

1. Clone or download this repository
2. Place the files in your web server directory
3. Ensure the `vendor` directory is present
4. Make sure the `en` and `tr` directories are writable by the web server

## Usage

1. Access the application through your web browser
2. Click "Choose Files" to select Excel files
3. Select your desired output format:
   - "Convert to JSON" for JSON format
   - "Convert to PHP Array" for PHP array format
4. The converted files will be saved in their respective language directories

## File Structure

```
ExcelLingua/
├── index.php          # Main interface
├── upload.php         # File processing logic
├── vendor/            # Required libraries
├── en/                # English translations
└── tr/                # Turkish translations
```

## Excel File Format

Your Excel files should follow this structure:
- First row should contain headers (language codes)
- First column should contain translation keys
- Subsequent columns should contain translations for each language

Example:
| Key | en | tr |
|-----|----|----|
| welcome | Welcome | Hoşgeldiniz |
| goodbye | Goodbye | Hoşçakalın |

## Security

- Maximum file size: 5MB
- Allowed file types: .xls, .xlsx
- Input sanitization
- XSS protection
- Secure file handling

## License

This project is open-source and available under the MIT License.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
