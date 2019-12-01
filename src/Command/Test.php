<?php
declare(strict_types=1);

namespace HZEX\Phinx\Command;

use InvalidArgumentException;
use Phinx\Migration\Manager\Environment;
use Phinx\Util\Util;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Option as InputOption;

/**
 * @author Leonid Kuzmin <lndkuzmin@gmail.com>
 */
class Test extends AbstractCommand
{
    protected static $defaultName = 'migrate:test';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(static::$defaultName);

        $this->addOption('--environment', '-e', InputOption::VALUE_REQUIRED, 'The target environment');

        $this->setDescription('Verify the configuration file')
            ->setHelp(
                <<<EOT
The <info>test</info> command verifies the YAML configuration file and optionally an environment

<info>phinx test</info>
<info>phinx test -e development</info>

EOT
            );
    }

    /**
     * Verify configuration file
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @return int 0 on success
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();
        $this->loadManager($input, $output);

        // Verify the migrations path(s)
        array_map(
            [$this, 'verifyMigrationDirectory'],
            Util::globAll($this->getConfig()->getMigrationPaths())
        );

        // Verify the seed path(s)
        array_map(
            [$this, 'verifySeedDirectory'],
            Util::globAll($this->getConfig()->getSeedPaths())
        );

        $envName = $input->getOption('environment');
        if ($envName) {
            if (!$this->getConfig()->hasEnvironment($envName)) {
                throw new InvalidArgumentException(sprintf(
                    'The environment "%s" does not exist',
                    $envName
                ));
            }

            $output->writeln(sprintf('<info>validating environment</info> %s', $envName));
            $environment = new Environment(
                $envName,
                $this->getConfig()->getEnvironment($envName)
            );
            // validate environment connection
            $environment->getAdapter()->connect();
        }

        $output->writeln('<info>success!</info>');

        return 0;
    }
}
