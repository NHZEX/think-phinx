<?php
declare(strict_types=1);

namespace HZEX\Phinx\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Option as InputOption;

class Status extends AbstractCommand
{
    protected static $defaultName = 'phinx:status';

    protected function configure()
    {
        $this->setName(static::$defaultName);

        $this->addOption('--environment', '-e', InputOption::VALUE_REQUIRED, 'The target environment.');
        $this->setDescription('Show migration status')
            ->addOption('--format', '-f', InputOption::VALUE_REQUIRED, 'The output format: text or json. Defaults to text.')
            ->setHelp(
                <<<EOT
The <info>status</info> command prints a list of all migrations, along with their current status

<info>phinx status -e development</info>
<info>phinx status -e development -f json</info>

The <info>version_order</info> configuration option is used to determine the order of the status migrations.
EOT
            );
    }

    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $environment = $input->getOption('environment');
        $format = $input->getOption('format');

        if ($environment === null) {
            $environment = $this->getConfig()->getDefaultEnvironment();
            $output->writeln('<comment>warning</comment> no environment specified, defaulting to: ' . $environment);
        } else {
            $output->writeln('<info>using environment</info> ' . $environment);
        }

        if (!$this->getConfig()->hasEnvironment($environment)) {
            $output->writeln(sprintf('<error>The environment "%s" does not exist</error>', $environment));

            return 1;
        }

        if ($format !== null) {
            $output->writeln('<info>using format</info> ' . $format);
        }

        $output->writeln('<info>ordering by </info>' . $this->getConfig()->getVersionOrder() . " time");

        // print the status
        return $this->getManager()->printStatus($environment, $format);
    }
}
