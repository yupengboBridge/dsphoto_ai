# DS Photo AI Management System

A Photo Management System (写真管理システム) built in PHP for managing and processing images.

## Features

- **Image Management**: Upload, edit, and delete photos with metadata management
- **Batch Processing**: Support for batch operations on multiple images
- **Thumbnail Generation**: Automatic generation of multiple thumbnail sizes
- **WebP Conversion**: Convert images to WebP format for better performance
- **Search & Filtering**: Advanced search with date range and keyword filtering
- **Export/Import**: CSV export/import capabilities for data management

## System Architecture

The system consists of two main applications:

1. **cms_photo_image**: CMS photo image management system
2. **photo_db**: Photo database management system

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- GD Library for image processing
- PHP Extensions: mysqli, mbstring, gd

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yupengboBridge/dsphoto_ai.git
```

2. Configure database settings in:
   - `cms_photo_image/config.php`
   - `photo_db/config.php`

3. Import database schema (if available)

4. Set proper permissions for upload directories:
```bash
chmod -R 777 cms_photo_image/uploads/
chmod -R 777 cms_photo_image/temporary/
chmod -R 777 cms_photo_image/thumb*/
chmod -R 777 photo_db/uploads/
chmod -R 777 photo_db/temporary/
chmod -R 777 photo_db/thumb*/
```

## Usage

Access the systems at:
- CMS Photo Image: `http://your-domain/cms_photo_image/`
- Photo DB: `http://your-domain/photo_db/`

## License

This project is proprietary software.

## Documentation

For more detailed information, see [CLAUDE.md](CLAUDE.md).