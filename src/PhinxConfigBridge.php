<?php
declare(strict_types=1);

namespace HZEX\Phinx;

use Phinx\Config\Config;
use think\App;

/**
 * Trait PhinxConfig
 * @package HZEX\Phinx
 */
trait PhinxConfigBridge
{
    protected $adapters = ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];

    /**
     * Parse the config file and load it into the config object
     *
     * @param App $app
     * @return Config
     */
    protected function loadConfig(App $app)
    {
        $phinx = $app->config->get('phinx', []);
        $config = new Config($phinx, $app->getConfigPath() . 'phinx.php');

        $db                               = app()->db;
        $name                             = $db->getConfig('default', 'mysql');
        $environments                     = $config['environments'];
        if (!isset($environments['default_environment'])) {
            $environments['default_environment'] = $name;
        }
        foreach ($db->getConfig('connections', []) as $name => $connection) {
            if (isset($environments[$name])) {
                continue;
            }
            if (in_array($connection['type'], $this->adapters)) {
                $adapter = $connection['type'];
            } elseif (isset($phinx['adapter_mapping'])
                && is_array($phinx['adapter_mapping'])
                && isset($phinx['adapter_mapping'][$connection['type']])
            ) {
                $adapter = $phinx['adapter_mapping'][$connection['type']];
            }
            if (isset($adapter)) {
                $environments[$name] = [
                    'adapter'      => $adapter,
                    'host'         => $connection['hostname'],
                    'name'         => $connection['database'],
                    'user'         => $connection['username'],
                    'pass'         => $connection['password'],
                    'port'         => $connection['hostport'],
                    'charset'      => $connection['charset'],
                    // 'collation'    => 'utf8_unicode_ci',
                    'table_prefix' => $connection['prefix'],
                ];
            } else {
                $environments[$name] = [
                    'connection' => $db->connect($name)->connect(),
                    'name' => $connection['database'],
                    'table_prefix' => $connection['prefix'],
                ];
            }
        }
        $config['environments'] = $environments;
        return $config;
    }
}
