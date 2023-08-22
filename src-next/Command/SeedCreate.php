<?php
/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Zxin\Think\Phinx\Command;

use Exception;
use InvalidArgumentException;
use Phinx\Config\NamespaceAwareInterface;
use Phinx\Util\Util;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument as InputArgument;
use think\console\input\Option as InputOption;

class SeedCreate extends AbstractCommand
{
    protected static $defaultName = 'seed:create';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName(static::$defaultName);

        $this->setDescription('Create a new database seeder')
            ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the seeder?')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Specify the path in which to create this seeder')
            ->setHelp(sprintf(
                '%sCreates a new database seeder%s',
                PHP_EOL,
                PHP_EOL
            ));

        // An alternative template.
        $this->addOption('template', 't', InputOption::VALUE_REQUIRED, 'Use an alternative template');
    }

    /**
     * Returns the seed path to create the seeder in.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return mixed
     * @throws Exception
     */
    protected function getSeedPath(InputInterface $input, OutputInterface $output)
    {
        // First, try the non-interactive option:
        $path = $input->getOption('path');

        if (!empty($path)) {
            return $path;
        }

        $paths = $this->getConfig()->getSeedPaths();

        // No paths? That's a problem.
        if (empty($paths)) {
            throw new Exception('No seed paths set in your Phinx configuration file.');
        }

        $paths = Util::globAll($paths);

        if (empty($paths)) {
            throw new Exception(
                'You probably used curly braces to define seed path in your Phinx configuration file, ' .
                'but no directories have been matched using this pattern. ' .
                'You need to create a seed directory manually.'
            );
        }

        // Only one path set, so select that:
        if (count($paths) === 1) {
            return array_shift($paths);
        }

        // Ask the user which of their defined paths they'd like to use:
        return $this->output->choice($this->input, 'Which seeds path would you like to use?', $paths, 0);
    }

    /**
     * Create the new seeder.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int 0 on success
     * @throws Exception
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        // get the seed path from the config
        $path = $this->getSeedPath($input, $output);

        if (!file_exists($path)) {
            if ($this->output->confirm($this->input, 'Create seeds directory? [y]/n ', true)) {
                mkdir($path, 0755, true);
            }
        }

        $this->verifySeedDirectory($path);

        $path = realpath($path);
        $className = $input->getArgument('name');

        if (!Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(sprintf(
                'The seed class name "%s" is invalid. Please use CamelCase format',
                $className
            ));
        }

        // Compute the file path
        $filePath = $path . DIRECTORY_SEPARATOR . $className . '.php';

        if (is_file($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'The file "%s" already exists',
                basename($filePath)
            ));
        }

        // Get the alternative template option from the command line.
        $altTemplate = $input->getOption('template');

        // Verify the alternative template file's existence.
        if ($altTemplate && !is_file($altTemplate)) {
            throw new InvalidArgumentException(sprintf(
                'The template file "%s" does not exist',
                $altTemplate
            ));
        }

        // Determine the appropriate mechanism to get the template
        // Load the alternative template if it is defined.
        $contents = file_get_contents($altTemplate ?: $this->getSeedTemplateFilename());

        $config = $this->getConfig();
        $namespace = $config instanceof NamespaceAwareInterface ? $config->getSeedNamespaceByPath($path) : null;
        $classes = [
            '$namespaceDefinition' => $namespace !== null ? ('namespace ' . $namespace . ';') : '',
            '$namespace' => $namespace,
            '$useClassName' => $config->getSeedBaseClassName(false),
            '$className' => $className,
            '$baseClassName' => $config->getSeedBaseClassName(true),
        ];
        $contents = strtr($contents, $classes);

        if (file_put_contents($filePath, $contents) === false) {
            throw new RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }

        $output->writeln('<info>using seed base class</info> ' . $classes['$useClassName']);
        $output->writeln('<info>created</info> ' . Util::relativePath($filePath));

        return self::CODE_SUCCESS;
    }
}
