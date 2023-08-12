<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
namespace Phinx\Console\Command;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'init')]
class Init extends Command
{
    protected const FILE_NAME = 'phinx';
    /**
     * @var string[]
     */
    protected static $supportedFormats = [\Phinx\Console\Command\AbstractCommand::FORMAT_JSON, \Phinx\Console\Command\AbstractCommand::FORMAT_YML_ALIAS, \Phinx\Console\Command\AbstractCommand::FORMAT_YML, \Phinx\Console\Command\AbstractCommand::FORMAT_PHP];
    /**
     * @var string|null
     */
    protected static $defaultName = 'init';
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Initialize the application for Phinx')->addOption('--format', '-f', InputArgument::OPTIONAL, 'What format should we use to initialize?', \Phinx\Console\Command\AbstractCommand::FORMAT_DEFAULT)->addArgument('path', InputArgument::OPTIONAL, 'Which path should we initialize for Phinx?')->setHelp(\sprintf('%sInitializes the application for Phinx%s', \PHP_EOL, \PHP_EOL));
    }
    /**
     * Initializes the application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input Interface implemented by all input classes.
     * @param \Symfony\Component\Console\Output\OutputInterface $output Interface implemented by all output classes.
     * @return int 0 on success
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $format = \strtolower($input->getOption('format'));
        $path = $this->resolvePath($input, $format);
        $this->writeConfig($path, $format);
        $output->writeln("<info>created</info> {$path}");
        return \Phinx\Console\Command\AbstractCommand::CODE_SUCCESS;
    }
    /**
     * Return valid $path for Phinx's config file.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input Interface implemented by all input classes.
     * @param string $format Format to resolve for
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function resolvePath(InputInterface $input, string $format) : string
    {
        // get the migration path from the config
        $path = (string) $input->getArgument('path');
        if (!\in_array($format, static::$supportedFormats, \true)) {
            throw new InvalidArgumentException(\sprintf('Invalid format "%s". Format must be either ' . \implode(', ', static::$supportedFormats) . '.', $format));
        }
        // Fallback
        if (!$path) {
            $path = \getcwd() . \DIRECTORY_SEPARATOR . self::FILE_NAME . '.' . $format;
        }
        // Adding file name if necessary
        if (\is_dir($path)) {
            $path .= \DIRECTORY_SEPARATOR . self::FILE_NAME . '.' . $format;
        }
        // Check if path is available
        $dirname = \dirname($path);
        if (\is_dir($dirname) && !\is_file($path)) {
            return $path;
        }
        // Path is valid, but file already exists
        if (\is_file($path)) {
            throw new InvalidArgumentException(\sprintf('Config file "%s" already exists.', $path));
        }
        // Dir is invalid
        throw new InvalidArgumentException(\sprintf('Invalid path "%s" for config file.', $path));
    }
    /**
     * Writes Phinx's config in provided $path
     *
     * @param string $path Config file's path.
     * @param string $format Format to use for config file
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return void
     */
    protected function writeConfig(string $path, string $format = \Phinx\Console\Command\AbstractCommand::FORMAT_DEFAULT) : void
    {
        // Check if dir is writable
        $dirname = \dirname($path);
        if (!\is_writable($dirname)) {
            throw new InvalidArgumentException(\sprintf('The directory "%s" is not writable', $dirname));
        }
        if ($format === \Phinx\Console\Command\AbstractCommand::FORMAT_YML_ALIAS) {
            $format = \Phinx\Console\Command\AbstractCommand::FORMAT_YML;
        }
        // load the config template
        if (\is_dir(__DIR__ . '/../../../../data')) {
            $contents = \file_get_contents(__DIR__ . '/../../../../data/' . self::FILE_NAME . '.' . $format . '.dist');
        } else {
            throw new RuntimeException(\sprintf('Could not find template for format "%s".', $format));
        }
        if (\file_put_contents($path, $contents) === \false) {
            throw new RuntimeException(\sprintf('The file "%s" could not be written to', $path));
        }
    }
}
