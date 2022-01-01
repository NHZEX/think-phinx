<?php
/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace HZEX\Phinx\Command;

use Phinx\Util\Util;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_keys;
use function array_map;
use function array_merge;
use function max;
use function sprintf;
use function str_pad;
use function str_repeat;

class ListAliases extends AbstractCommand
{
    protected static $defaultName = 'list:aliases';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName(static::$defaultName);

        $this->setDescription('List template class aliases')
            ->setHelp('The <info>list:aliases</info> command lists the migration template generation class aliases');
    }

    /**
     * {@inheritDoc}
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
                    Util::relativePath($this->config->getConfigFilePath())
                )
            );
        }

        return self::CODE_SUCCESS;
    }
}
