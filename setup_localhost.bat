@echo off
echo MeetScribe - Local Deployment Setup
echo ===================================
echo.

echo Checking for XAMPP installation...
if exist "C:\xampp" (
    echo XAMPP found at C:\xampp
) else (
    echo XAMPP not found at default location.
    echo Please make sure XAMPP is installed and try again.
    echo You can download XAMPP from: https://www.apachefriends.org/
    pause
    exit /b
)

echo.
echo Setting up PostgreSQL database...
echo You'll need to have PostgreSQL installed and running.
echo.
echo Default database settings in includes/config.php:
echo - Host: localhost
echo - Port: 5432
echo - User: postgres
echo - Database: meetscribe
echo.
echo Make sure to update includes/config.php if your settings are different.
echo.
echo Press any key to continue with setup...
pause > nul

echo.
echo Checking environment variables...
echo If you want to use environment variables, you should set:
echo - PGHOST
echo - PGPORT
echo - PGUSER
echo - PGPASSWORD
echo - PGDATABASE
echo - DATABASE_URL
echo - ASSEMBLY_API_KEY
echo.

echo Please review the following instructions:
echo.
echo 1. Make sure PostgreSQL is running
echo 2. Create a database named 'meetscribe'
echo 3. Update database settings in includes/config.php if needed
echo 4. Place this project in C:\xampp\htdocs\MeetScribeHub (or any folder name)
echo 5. Start Apache in XAMPP Control Panel
echo 6. Open http://localhost/MeetScribeHub/ in your browser
echo.

echo Path Handling Update:
echo ---------------------
echo The application now automatically detects if it's running in a subdirectory
echo All links will work correctly without manual modification
echo.

echo For testing transcription:
echo - USE_TEST_TRANSCRIPT is set to true by default (in config.php)
echo - For real transcription, get an Assembly AI API key
echo.

echo Setup instructions complete. Ready to access from localhost!
echo.
pause