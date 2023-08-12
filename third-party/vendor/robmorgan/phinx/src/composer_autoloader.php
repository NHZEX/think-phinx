<?php

namespace _Z_PhinxVendor;

/**
 * Attempts to load Composer's autoload.php as either a dependency or a
 * stand-alone package.
 *
 * @return bool
 */
return function () {
    $files = [
        __DIR__ . '/../../../autoload.php',
        // composer dependency
        __DIR__ . '/../vendor/autoload.php',
    ];
    foreach ($files as $file) {
        if (\is_file($file)) {
            require_once $file;
            return \true;
        }
    }
    return \false;
};
