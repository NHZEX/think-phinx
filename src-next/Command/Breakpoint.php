<?php
/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Zxin\Think\Phinx\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Option as InputOption;

class Breakpoint extends AbstractCommand
{
    protected static $defaultName = 'migrate:breakpoint';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(static::$defaultName);

        $this->addOption('--environment', '-e', InputOption::VALUE_REQUIRED, 'The target environment.');
        $this->setDescription('Manage breakpoints')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to target for the breakpoint')
            ->addOption('--set', '-s', InputOption::VALUE_NONE, 'Set the breakpoint')
            ->addOption('--unset', '-u', InputOption::VALUE_NONE, 'Unset the breakpoint')
            ->addOption('--remove-all', '-r', InputOption::VALUE_NONE, 'Remove all breakpoints')
            ->setHelp(
                <<<EOT
The <info>breakpoint</info> command allows you to toggle, set, or unset a breakpoint against a specific target to inhibit rollbacks beyond a certain target.
If no target is supplied then the most recent migration will be used.
You cannot specify un-migrated targets

<info>phinx breakpoint -e development</info>
<info>phinx breakpoint -e development -t 20110103081132</info>
<info>phinx breakpoint -e development -r</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $environment = $input->getOption('environment');
        $version = (int)$input->getOption('target') ?: null;
        $removeAll = $input->getOption('remove-all');
        $set = $input->getOption('set');
        $unset = $input->getOption('unset');

        if ($environment === null) {
            $environment = $this->getConfig()->getDefaultEnvironment();
            $output->writeln('<comment>warning</comment> no environment specified, defaulting to: ' . $environment);
        } else {
            $output->writeln('<info>using environment</info> ' . $environment);
        }

        if (!$this->getConfig()->hasEnvironment($environment)) {
            $output->writeln(sprintf('<error>The environment "%s" does not exist</error>', $environment));

            return self::CODE_ERROR;
        }

        if ($version && $removeAll) {
            throw new InvalidArgumentException('Cannot toggle a breakpoint and remove all breakpoints at the same time.');
        }

        if (($set && $unset) || ($set && $removeAll) || ($unset && $removeAll)) {
            throw new InvalidArgumentException('Cannot use more than one of --set, --unset, or --remove-all at the same time.');
        }

        if ($removeAll) {
            // Remove all breakpoints.
            $this->getManager()->removeBreakpoints($environment);
        } elseif ($set) {
            // Set the breakpoint.
            $this->getManager()->setBreakpoint($environment, $version);
        } elseif ($unset) {
            // Unset the breakpoint.
            $this->getManager()->unsetBreakpoint($environment, $version);
        } else {
            // Toggle the breakpoint.
            $this->getManager()->toggleBreakpoint($environment, $version);
        }

        return self::CODE_SUCCESS;
    }
}
