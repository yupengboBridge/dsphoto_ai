# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Photo Management System (写真管理システム) built in PHP. The system consists of two main applications:
- **cms_photo_image**: CMS photo image management system
- **photo_db**: Photo database management system

Both applications share similar architecture and functionality for managing, storing, and processing photo images.

## Key Architecture Components

### Database
- MySQL database named `photodb_image`
- Main tables include:
  - `photoimg`: Main photo information storage
  - `photo_log` / `photo_log_cms`: Access and operation logs
  - User and permission management tables

### Core PHP Files
- `config.php`: Database connections and system configuration
- `lib.php`: Core functions for image processing, database operations
- `dblink.php`: Database connection utilities
- `budlib.php`: Additional utility functions

### Image Processing
- Automatic thumbnail generation in multiple sizes (thumb1-thumb13 directories)
- WebP conversion support
- Image resizing for PC/SP versions
- Credit/watermark functionality

### Authentication & Security
- Session-based authentication system
- Multiple security levels (1-4)
- Group-based permissions
- Login required for most operations

## Development Commands

### Local Development Setup
```bash
# Database connection settings are in config.php
# Default: localhost, root, root@Hcst2022

# Access the systems at:
# CMS: http://cmsphotoimg.hcstec.com/
# PhotoDB: http://photodb.hcstec.com/
```

### Common Operations
```bash
# Check PHP configuration
php phpinfo.php

# Database management
# Use db_managemnt.php for database operations

# Image batch processing
php convert_all_webp_cron.php  # Convert images to WebP
php make_thumb3.php            # Generate thumbnails
```

### File Upload Directories
- `uploads/`: Main image storage
- `temporary/`: Temporary file storage
- `thumb1-13/`: Generated thumbnails
- `change/`: Modified images
- `csv/`: CSV data files
- `log/`: System logs

## Important Notes

1. **Character Encoding**: UTF-8 throughout the system
2. **File Size Limits**: 5-6MB max upload size configured
3. **Image Processing**: GD library required for image manipulation
4. **Session Management**: Required for authentication
5. **Directory Permissions**: Ensure write permissions on upload/thumb directories

## Key Features

1. **Image Management**
   - Upload, edit, delete photos
   - Batch operations support
   - Metadata management

2. **Search & Filtering**
   - Multiple search interfaces (kikan, result, etc.)
   - Date range filtering
   - Keyword search with N-gram support

3. **Export/Import**
   - CSV export functionality
   - Batch import capabilities
   - XML generation for various data formats

4. **Integration Points**
   - SOAP web services for external systems
   - FTP/SFTP support for file transfers
   - Multiple API endpoints

## Testing

While no formal test suite exists, test files are available:
- `test.php`, `testaa.php`: General testing
- `image_search_kikan_test.php`: Search functionality testing
- `search_result_test*.php`: Search result testing

## Troubleshooting

1. Check `config.php` for correct database settings
2. Verify PHP extensions: GD, MySQL, mbstring
3. Check directory permissions for uploads/thumbnails
4. Review logs in `log/` directory for errors