<?php
declare(strict_types=1);

namespace HZEX\Phinx\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_keys;
use function array_map;
use function array_merge;
use function getcwd;
use function max;
use function realpath;
use function sprintf;
use function str_pad;
use function str_repeat;
use function str_replace;

class ListAliases extends AbstractCommand
{
    protected static $defaultName = 'list:aliases';

    protected function configure()
    {
        $this->setName(static::$defaultName);

        $this->setDescription('List template class aliases')
            ->setHelp('The <info>list:aliases</info> command lists the migration template generation class aliases');
    }

    /**
     * @inheritDoc
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input, $output);

        $aliases = $this->config->getAliases();

        if ($aliases) {
            $maxAliasLength = max(array_map('strlen', array_keys($aliases)));
            $maxClassLength = max(array_map('strlen', $aliases));
            $output->writeln(
                array_merge(
                    [
                        '',
                        sprintf('%s %s', str_pad('Alias', $maxAliasLength), str_pad('Class', $maxClassLength)),
                        sprintf('%s %s', str_repeat('=', $maxAliasLength), str_repeat('=', $maxClassLength)),
                    ],
                    array_map(
                        function ($alias, $class) use ($maxAliasLength, $maxClassLength) {
                            return sprintf('%s %s', str_pad($alias, $maxAliasLength), str_pad($class, $maxClassLength));
                        },
                        array_keys($aliases),
                        $aliases
                    )
                )
            );
        } else {
            $output->writeln(
                sprintf(
                    '<error>No aliases defined in %s</error>',
                    str_replace(getcwd(), '', realpath($this->config->getConfigFilePath()))
                )
            );
        }

        return 0;
    }
}
