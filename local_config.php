<?php

// Use SQLite as the database
define('DB_TYPE_OVERRIDE', 'sqlite');

// Set the full path to your SQLite database file
define('DB_NAME_OVERRIDE', __DIR__ . '/database/meetscribe.db');

// Disable unused PostgreSQL settings
define('DB_HOST_OVERRIDE', null);
define('DB_PORT_OVERRIDE', null);
define('DB_USER_OVERRIDE', null);
define('DB_PASS_OVERRIDE', null);


// Assembly AI settings (optional - update as needed)
define('ASSEMBLY_API_KEY_OVERRIDE', '594050f8c9ae451e843e42cfbe828165');

// Development mode (true uses sample transcript instead of real API call)
define('USE_TEST_TRANSCRIPT_OVERRIDE', false);

?>
