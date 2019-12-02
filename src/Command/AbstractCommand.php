<?php
declare(strict_types=1);

namespace HZEX\Phinx\Command;

use HZEX\Phinx\ConsoleBridge\ThinkInput;
use HZEX\Phinx\ConsoleBridge\ThinkOutput;
use HZEX\Phinx\PhinxConfigBridge;
use InvalidArgumentException;
use Phinx\Config\ConfigInterface;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\Manager;
use Phinx\Util\Util;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use UnexpectedValueException;

abstract class AbstractCommand extends Command
{
    use PhinxConfigBridge;

    /**
     * The location of the default migration template.
     */
    const DEFAULT_MIGRATION_TEMPLATE = '/Migration/Migration.template.php.dist';

    /**
     * The location of the default seed template.
     */
    const DEFAULT_SEED_TEMPLATE = '/Seed/Seed.template.php.dist';

    /**
     * @var string|null The default command name
     */
    protected static $defaultName;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var ThinkInput
     */
    protected $inputBridge;

    /**
     * @var ThinkOutput
     */
    protected $outputBridge;

    /**
     * @return string|null
     */
    public static function getDefaultName(): ?string
    {
        return static::$defaultName;
    }

    /**
     * @param Input  $input
     * @param Output $output
     * @return int|void|null
     */
    protected function execute(Input $input, Output $output)
    {
        $this->inputBridge = new ThinkInput($input);
        $this->outputBridge = new ThinkOutput($output);
        return $this->handle($this->inputBridge, $this->outputBridge);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return mixed
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output);

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function bootstrap(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getConfig()) {
            $this->setConfig($this->loadConfig($this->app));
        }

        $this->loadManager($this->inputBridge, $this->outputBridge);

        // report the paths
        $paths = $this->getConfig()->getMigrationPaths();

        $output->writeln('<info>using migration paths</info> ');

        foreach (Util::globAll($paths) as $path) {
            $output->writeln('<info> - ' . realpath($path) . '</info>');
        }

        try {
            $paths = $this->getConfig()->getSeedPaths();

            $output->writeln('<info>using seed paths</info> ');

            foreach (Util::globAll($paths) as $path) {
                $output->writeln('<info> - ' . realpath($path) . '</info>');
            }
        } catch (UnexpectedValueException $e) {
            // do nothing as seeds are optional
        }
    }

    /**
     * Load the migrations manager and inject the config
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function loadManager(InputInterface $input, OutputInterface $output)
    {
        if ($this->getManager() === null) {
            $manager = new Manager($this->getConfig(), $input, $output);
            $this->setManager($manager);
        } else {
            $manager = $this->getManager();
            $manager->setInput($input);
            $manager->setOutput($output);
        }
    }

    /**
     * Verify that the migration directory exists and is writable.
     *
     * @param string $path
     * @throws InvalidArgumentException
     * @return void
     */
    protected function verifyMigrationDirectory($path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'Migration directory "%s" does not exist',
                $path
            ));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf(
                'Migration directory "%s" is not writable',
                $path
            ));
        }
    }

    /**
     * Verify that the seed directory exists and is writable.
     *
     * @param string $path
     * @throws InvalidArgumentException
     * @return void
     */
    protected function verifySeedDirectory($path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'Seed directory "%s" does not exist',
                $path
            ));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf(
                'Seed directory "%s" is not writable',
                $path
            ));
        }
    }

    /**
     * Returns the migration template filename.
     *
     * @return string
     */
    protected function getMigrationTemplateFilename()
    {
        return $this->app->getRootPath() . 'vendor/robmorgan/phinx/src/Phinx' . self::DEFAULT_MIGRATION_TEMPLATE;
    }

    /**
     * Returns the seed template filename.
     *
     * @return string
     */
    protected function getSeedTemplateFilename()
    {
        return $this->app->getRootPath() . 'vendor/robmorgan/phinx/src/Phinx' . self::DEFAULT_SEED_TEMPLATE;
    }

    /**
     * Sets the config.
     *
     * @param ConfigInterface $config
     * @return AbstractCommand
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Gets the config.
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the migration manager.
     *
     * @param Manager $manager
     * @return AbstractCommand
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Gets the migration manager.
     *
     * @return Manager|null
     */
    public function getManager()
    {
        return $this->manager;
    }
}
