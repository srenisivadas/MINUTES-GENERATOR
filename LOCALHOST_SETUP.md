# MeetScribe - Local Setup Guide

This guide will help you set up MeetScribe on your local development environment.

## Prerequisites

- Web server (Apache/Nginx/XAMPP)
- PHP 7.4+ with PDO extension
- PostgreSQL database (preferred) or SQLite
- Composer (optional, for dependency management)

## Setup Steps

### 1. Database Configuration

#### PostgreSQL (Recommended)

1. Install PostgreSQL if not already installed
2. Create a new database:
   ```sql
   CREATE DATABASE meetscribe;
   ```
3. Update database settings in `local_config.php`:
   ```php
   define('DB_TYPE_OVERRIDE', 'pgsql');
   define('DB_HOST_OVERRIDE', 'localhost');
   define('DB_PORT_OVERRIDE', '5432');
   define('DB_USER_OVERRIDE', 'your_username');
   define('DB_PASS_OVERRIDE', 'your_password');
   define('DB_NAME_OVERRIDE', 'meetscribe');
   ```

#### SQLite (Alternative)

1. The application has fallback support for SQLite
2. Ensure the `database` directory exists and is writable
3. Update settings in `local_config.php`:
   ```php
   define('DB_TYPE_OVERRIDE', 'sqlite');
   ```

### 2. Web Server Setup

#### Using XAMPP

1. Install XAMPP if not already installed
2. Copy the project files to:
   ```
   C:\xampp\htdocs\MeetScribeHub\
   ```
   (or any other folder name under htdocs)
3. Start Apache in XAMPP Control Panel
4. Access the application at:
   ```
   http://localhost/MeetScribeHub/
   ```

#### Using Apache Directly

1. Configure a virtual host:
   ```apache
   <VirtualHost *:80>
       ServerName meetscribe.local
       DocumentRoot "/path/to/MeetScribeHub"
       <Directory "/path/to/MeetScribeHub">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Add the following to your hosts file:
   ```
   127.0.0.1 meetscribe.local
   ```
3. Restart Apache
4. Access the application at:
   ```
   http://meetscribe.local/
   ```

### 3. Assembly AI API Key

For transcription functionality, you need an Assembly AI API key:

1. Sign up at [Assembly AI](https://www.assemblyai.com/)
2. Get your API key from the dashboard
3. Update settings in `local_config.php`:
   ```php
   define('ASSEMBLY_API_KEY_OVERRIDE', 'your_assembly_ai_api_key');
   ```

### 4. Testing Mode

By default, the application uses a test transcript instead of making actual API calls:

1. To continue using test mode (recommended for development):
   ```php
   define('USE_TEST_TRANSCRIPT_OVERRIDE', true);
   ```
2. To use the real Assembly AI API:
   ```php
   define('USE_TEST_TRANSCRIPT_OVERRIDE', false);
   ```

### 5. Troubleshooting

#### Database Connection Issues

- Ensure PostgreSQL is running
- Verify username, password and database exist
- Check port configuration (default is 5432)

#### Path/URL Issues

- The application automatically detects paths and builds URLs correctly
- If you experience URL issues, clear browser cache and restart the web server

#### API Issues

- Verify your Assembly AI API key is correct
- Check API call logs in the browser console
- Enable `USE_TEST_TRANSCRIPT_OVERRIDE` to bypass API calls

## First-time Run

1. Access the application in your web browser
2. The database tables will be created automatically
3. Create a new account through the signup page
4. Upload an audio file to test transcription

## Deployment Tips

1. For production deployment:
   - Set `display_errors` to 0 in `php.ini`
   - Update error handling in `config.php`
   - Use HTTPS for secure connections
   - Consider implementing rate limiting

2. Database migrations:
   - The application handles database schema automatically
   - Back up your data before major updates

## Contact

For issues or questions, please open an issue in the project repository.