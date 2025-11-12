<?php

// We need to require the files to make the classes available.
// (In a real project, Composer's autoloader handles this automatically)
require_once 'App/Utils/Logger.php';
require_once 'App/Database/Logger.php';

// Import the Utils Logger
use App\Utils\Logger;

// What if we want to use both? We can give one an alias.
use App\Database\Logger as DatabaseLogger;

// Now we can use short names
Logger::log("This is a utility message.");
DatabaseLogger::log("Database query executed.");
