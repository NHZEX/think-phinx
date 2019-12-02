<?php
declare(strict_types=1);

namespace TestPhinx\Migration;

use HZEX\Phinx\PhinxConfigBridge;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use think\console\Input;
use think\console\Output;

class ManagerTest extends TestCase
{
    use PhinxConfigBridge;

    protected $app;

    protected $config;

    protected $manager;

    protected $defaultDb;

    protected function setUp(): void
    {
        $this->app = app();
        $this->config = $this->app->config;

        $this->defaultDb = $this->app->db->getConfig('default');

        $this->config->set([
            'paths' => [
                'migrations' => [
                    // 'DbMigrations' => './.phinx/migrations',
                ],
                'seeds' => [
                    // 'DbSeeds' => './.phinx/seeds',
                ]
            ],
            'environments' => [
                'default_migration_table' => '_phinxlog',
            ],
            'version_order' => 'creation',
        ], 'phinx');

        $this->manager = new Manager($this->loadConfig($this->app), new ArrayInput([]), new NullOutput());
    }

    protected function setPhinxPaths($migrations, $seeds)
    {
        if (!is_array($migrations)) {
            $migrations = [$migrations];
        }
        $this->config->set([
            'paths' => [
                'migrations' => $migrations,
                'seeds' => $seeds,
            ],
        ], 'phinx');
    }

    /**
     * @param null $env
     * @return AdapterInterface
     */
    public function getAdapter($env = null)
    {
        return $this->manager->getEnvironment($env ?? $this->defaultDb)->getAdapter();
    }

    public function migrateCallsProvider()
    {
        return [
            ['migrate:test', [], 0],
            ['migrate:status', [], Manager::EXIT_STATUS_DOWN],
            ['migrate:run', [], 0],
            ['migrate:status', [], 0],
            ['migrate:rollback', ['-t', '0'], 0],
            ['migrate:status', [], Manager::EXIT_STATUS_DOWN],
        ];
    }

    /**
     * @dataProvider migrateCallsProvider
     * @param $command
     * @param $args
     * @param $code
     */
    public function testMigrateConsole(string $command, array $args, int $code)
    {
        $this->setPhinxPaths(__DIR__ . '/../_files/reversiblemigrations', __DIR__ . '/../_files/empty_seed');

        $this->call($command, $args, $exitCode, 'console');
        $this->assertEquals($code, $exitCode, "call {$command} fail");
    }

    /**
     * @dataProvider migrateCallsProvider
     * @param $command
     * @param $args
     * @param $code
     */
    public function testMigrateConsole2(string $command, array $args, int $code)
    {
        $this->setPhinxPaths(__DIR__ . '/../_files/drop_index_regression', __DIR__ . '/../_files/empty_seed');

        $this->call($command, $args, $exitCode, 'console');
        $this->assertEquals($code, $exitCode, "call {$command} fail");
    }

    /**
     * @dataProvider migrateCallsProvider
     * @param $command
     * @param $args
     * @param $code
     */
    public function testMigrateSchema(string $command, array $args, int $code)
    {
        $this->setPhinxPaths([
            'DbMigrations' => __DIR__ . '/../_files/schema_definition',
        ], __DIR__ . '/../_files/empty_seed');

        $this->call($command, $args, $exitCode, 'console');
        $this->assertEquals($code, $exitCode, "call {$command} fail");
    }

    /**
     * @param string $command
     * @param array  $parameters
     * @param int    $exitCode
     * @param string $driver
     * @return Output
     */
    public function call(string $command, array $parameters = [], &$exitCode = 0, string $driver = 'buffer')
    {
        array_unshift($parameters, $command);

        $input  = new Input($parameters);
        $output = new Output($driver);

        $this->app->console->setCatchExceptions(false);
        $this->app->console->setAutoExit(false);
        $exitCode = $this->app->console->find($command)->run($input, $output);

        // buffer->fetch()
        return $output;
    }
}
