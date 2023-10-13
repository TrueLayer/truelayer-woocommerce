<?php
// Define ABSPATH to prevent early exit from files.
define( 'ABSPATH', '.' );

// Set the path to the root of the plugin
define( 'PLUGIN_ROOT', dirname( __DIR__, 2 ) );

// Add a global mock_settings variable.
global $mock_settings;

// Include the autoloader.
require_once PLUGIN_ROOT . '/vendor/autoload.php';

// Include the mock file.
require_once 'mock.php';
