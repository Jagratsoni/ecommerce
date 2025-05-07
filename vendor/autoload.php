<?php
// Simple autoloader for Razorpay SDK
spl_autoload_register(function ($class) {
    // Razorpay namespace
    if (strpos($class, 'Razorpay\\') === 0) {
        $class = str_replace('Razorpay\\', '', $class);
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'razorpay-php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class . '.php';
        
        // Debug information
        error_log("Trying to load class: " . $class);
        error_log("Looking for file: " . $file);
        
        if (file_exists($file)) {
            error_log("File found, including it");
            require_once $file;
            return true;
        } else {
            error_log("File not found");
        }
    }
    return false;
}); 