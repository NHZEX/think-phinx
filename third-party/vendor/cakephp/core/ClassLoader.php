<?php

declare (strict_types=1);
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace _Z_PhinxVendor\Cake\Core;

/**
 * ClassLoader
 *
 * @deprecated 4.0.3 Use composer to generate autoload files instead.
 */
class ClassLoader
{
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array<string, array>
     */
    protected $_prefixes = [];
    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function register() : void
    {
        /** @var callable $callable */
        $callable = [$this, 'loadClass'];
        \spl_autoload_register($callable);
    }
    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $baseDir A base directory for class files in the
     * namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     * instead of appending it; this causes it to be searched first rather
     * than last.
     * @return void
     */
    public function addNamespace(string $prefix, string $baseDir, bool $prepend = \false) : void
    {
        $prefix = \trim($prefix, '\\') . '\\';
        $baseDir = \rtrim($baseDir, '/') . \DIRECTORY_SEPARATOR;
        $baseDir = \rtrim($baseDir, \DIRECTORY_SEPARATOR) . '/';
        $this->_prefixes[$prefix] = $this->_prefixes[$prefix] ?? [];
        if ($prepend) {
            \array_unshift($this->_prefixes[$prefix], $baseDir);
        } else {
            $this->_prefixes[$prefix][] = $baseDir;
        }
    }
    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     * @return string|false The mapped file name on success, or boolean false on
     * failure.
     */
    public function loadClass(string $class)
    {
        $prefix = $class;
        while (($pos = \strrpos($prefix, '\\')) !== \false) {
            $prefix = \substr($class, 0, $pos + 1);
            $relativeClass = \substr($class, $pos + 1);
            $mappedFile = $this->_loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }
            $prefix = \rtrim($prefix, '\\');
        }
        return \false;
    }
    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relativeClass The relative class name.
     * @return string|false Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function _loadMappedFile(string $prefix, string $relativeClass)
    {
        if (!isset($this->_prefixes[$prefix])) {
            return \false;
        }
        foreach ($this->_prefixes[$prefix] as $baseDir) {
            $file = $baseDir . \str_replace('\\', \DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if ($this->_requireFile($file)) {
                return $file;
            }
        }
        return \false;
    }
    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @return bool True if the file exists, false if not.
     */
    protected function _requireFile(string $file) : bool
    {
        if (\file_exists($file)) {
            require $file;
            return \true;
        }
        return \false;
    }
}
