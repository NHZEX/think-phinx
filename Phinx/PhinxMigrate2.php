<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/21
 * Time: 14:53
 */

namespace Phinx;

use Phinx\Console\Command\Migrate as Migrate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\facade\App;

class PhinxMigrate2 extends Migrate
{
    protected static $defaultName = 'migrate';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function loadConfig(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasOption('configuration')) {
            $input->setOption('configuration', App::getRootPath() . 'phinx.php');
        }
        parent::loadConfig($input, $output);
    }
}
