<?php
declare(strict_types=1);

// Run in a fresh PHP process: PHPUnit/ThinkPHP's bootstrap must not mask load-order bugs.
$functionsBefore = get_defined_functions()['user'];
$constantsBefore = get_defined_constants(true)['user'] ?? [];
if (in_array('--predefined-env', $argv, true)) {
    function env($name = null, $default = null) { return 'think-phinx-env-sentinel'; }
}
require __DIR__ . '/../vendor/autoload.php';

$failures = [];
// ThinkPHP loads its helpers during application startup, not Composer autoload.
$frameworkPath = realpath(Composer\InstalledVersions::getInstallPath('topthink/framework'));
require_once $frameworkPath . '/src/helper.php';
if (!function_exists('env')) {
    $failures[] = 'ThinkPHP env() is missing.';
} else {
    $file = str_replace('\\', '/', (new ReflectionFunction('env'))->getFileName());
    if (in_array('--predefined-env', $argv, true)) {
        if (env() !== 'think-phinx-env-sentinel') {
            $failures[] = 'An existing env() implementation was replaced.';
        }
    } elseif (strpos($file, str_replace('\\', '/', $frameworkPath) . '/') !== 0) {
        $failures[] = 'env() was defined outside ThinkPHP: ' . $file;
    }
}

if (!class_exists(Phinx\Migration\AbstractMigration::class)) {
    $failures[] = 'The public Phinx migration API cannot be autoloaded.';
}
// Load the ORM explicitly: incompatible helper contracts can otherwise hide until boot.
if (!class_exists(think\Model::class)) {
    $failures[] = 'The ThinkPHP ORM model API cannot be autoloaded.';
}
if (!class_exists(HZEX\Phinx\Schema::class)) {
    $failures[] = 'The legacy HZEX schema API cannot be autoloaded.';
}
if (Composer\InstalledVersions::getPrettyVersion('robmorgan/phinx') !== '0.16.12') {
    $failures[] = 'The installed Phinx version is not 0.16.12.';
}

$dependencyPaths = [];
foreach (Composer\InstalledVersions::getInstalledPackages() as $package) {
    if ($package === 'robmorgan/phinx' || strpos($package, 'cakephp/') === 0) {
        $path = Composer\InstalledVersions::getInstallPath($package);
        if ($path !== null) {
            $dependencyPaths[] = str_replace('\\', '/', realpath($path) ?: $path) . '/';
        }
    }
}
// Check every newly loaded global function, rather than only CakePHP's historical env().
foreach (array_diff(get_defined_functions()['user'], $functionsBefore) as $function) {
    if (strpos($function, '\\') !== false) {
        continue;
    }
    $file = str_replace('\\', '/', (new ReflectionFunction($function))->getFileName() ?: '');
    foreach ($dependencyPaths as $path) {
        if (strpos($file, $path) === 0) {
            $failures[] = 'Unexpected global function from Phinx/CakePHP: ' . $function;
            break;
        }
    }
}

// CakePHP 5 still defines these constants; they are not global helper functions.
$allowedConstants = ['DS', 'CAKE_DATE_RFC7231'];
$newConstants = array_diff_key(get_defined_constants(true)['user'] ?? [], $constantsBefore);
$cakeConstants = array_intersect_key($newConstants, array_flip($allowedConstants));
if ($cakeConstants) {
    echo 'Known CakePHP constants: ' . implode(', ', array_keys($cakeConstants)) . PHP_EOL;
}
if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "Composer autoload and global-function isolation passed.\n";
