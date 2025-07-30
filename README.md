# Database Viewer Application

A PHP-based web application for viewing MySQL database data with a clean Bootstrap interface.

## Features

- **Database Navigation**: Browse all available databases on your MySQL server
- **Table Listing**: View all tables within a selected database
- **Data Viewing**: Display table data with pagination (15 records per page)
- **Table Structure**: View detailed table structure including field types, keys, and constraints
- **Responsive Design**: Clean Bootstrap 5 interface that works on desktop and mobile
- **Security**: Uses prepared statements and proper input sanitization
- **Environment Variables**: Database credentials stored securely in `.env` file

## Prerequisites

- PHP 7.4+ with PDO MySQL extension
- MySQL/MariaDB server
- Web server (Apache/Nginx) or PHP built-in server
- XAMPP (if using the provided setup)

## Installation

1. **Database Configuration**: 
   - The application uses the `.env` file for database credentials

2. **Access the Application**:
   - If using XAMPP: `http://localhost/DatabaseViewer/`
   - If using PHP built-in server: `php -S localhost:8000`

## File Structure

```
database viewer/
├── .env                 # Database credentials (keep secure!)
├── config.php          # Database configuration and helper functions
├── index.php           # Main application interface
└── README.md           # This documentation
```

## Usage

1. **Select Database**: Click on any database from the left sidebar to view its tables
2. **View Tables**: Once a database is selected, tables will appear in the sidebar
3. **Browse Data**: Click on a table to view its data in the main content area
4. **Pagination**: Use the pagination controls at the bottom to navigate through large datasets
5. **Table Structure**: Click the "Structure" button to view detailed table schema information

## Security Features

- **Environment Variables**: Sensitive database credentials are stored in `.env` file
- **Prepared Statements**: All database queries use prepared statements to prevent SQL injection
- **Input Sanitization**: All user inputs are properly sanitized and validated
- **HTML Escaping**: All output is escaped to prevent XSS attacks
- **System Database Filtering**: System databases (information_schema, performance_schema, mysql, sys) are hidden by default

## Customization

### Changing Records Per Page
Edit the `$limit` variable in `index.php` (currently set to 15):
```php
$limit = 15; // Records per page
```

### Adding New Features
The application is modular and can be extended by:
- Adding new functions to `config.php`
- Modifying the interface in `index.php`
- Creating additional PHP files for specific functionality

### Styling
The application uses Bootstrap 5 with custom CSS. Modify the `<style>` section in `index.php` to customize the appearance.

## Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Verify database credentials in `.env`
   - Ensure MySQL server is running
   - Check firewall settings for remote connections

2. **No Databases Showing**:
   - Verify user permissions on MySQL server
   - Check if user has SELECT privileges on databases

3. **PHP Errors**:
   - Ensure PHP PDO MySQL extension is installed
   - Check PHP error logs for detailed error messages

### Error Messages

- **".env file not found"**: Ensure the `.env` file exists in the same directory as `config.php`
- **"Database connection failed"**: Check database credentials and server availability
- **"No data found in this table"**: The selected table is empty or user lacks SELECT privileges

## Security Recommendations

1. **Never commit `.env` file** to version control
2. **Use HTTPS** in production environments
3. **Limit database user permissions** to only what's necessary
4. **Regular security updates** for PHP and MySQL
5. **Access control** - consider adding authentication for production use

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

This is a custom database viewer application. Use at your own discretion and ensure proper security measures in production environments.
