<?php

/**
 * @see http://www.php-fig.org/psr/psr-4/examples/
 */
spl_autoload_register(function ($class) {

    $prefix = 'CcCedict\\';
    $base_dir = __DIR__ . '/../src/CcCedict/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
