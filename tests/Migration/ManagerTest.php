<?php
declare(strict_types=1);

namespace Test\Phinx\Migration;

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
        $this->app    = app();
        $this->config = $this->app->config;

        $this->defaultDb = $this->app->db->getConfig('default');

        $this->config->set([
            'paths'         => [
                'migrations' => [
                ],
                'seeds'      => [
                ]
            ],
            'environments'  => [
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
                'seeds'      => $seeds,
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

    public function testCreate()
    {
        $tmp = sys_get_temp_dir() . '/_test/';
        foreach (glob($tmp . '*/*.php') as $file) {
            @unlink($file);
        }
        @mkdir($tmp . 'migrations');
        @mkdir($tmp . 'seeds');
        $this->setPhinxPaths($tmp . 'migrations', $tmp . 'seeds');

        $this->call('migrate:create', ['TestMigration'], $exitCode, 'console');
        $this->assertEquals(0, $exitCode, "call migrate:create fail");

        $this->assertTrue(glob($tmp . 'migrations/*_test_migration.php') >= 1);

        $this->call('seed:create', ['SeedMigration'], $exitCode, 'console');
        $this->assertEquals(0, $exitCode, "call seed:create fail");

        $this->assertTrue(is_file($tmp . 'seeds/SeedMigration.php'));
    }

    public function testMigrate()
    {
        $this->setPhinxPaths(__DIR__ . '/../_files/reversiblemigrations', __DIR__ . '/../_files/empty_seed');
        $this->callMigrate('test', [], 0, 'console');
    }

    /**
     */
    public function testReversibleMigrationsWorkAsExpected()
    {
        $this->setPhinxPaths(__DIR__ . '/../_files/reversiblemigrations', __DIR__ . '/../_files/empty_seed');

        $adapter = $this->getAdapter();
        $adapter->dropDatabase($adapter->getOption('name'));
        $adapter->createDatabase($adapter->getOption('name'));
        $adapter->disconnect();

        $this->callMigrate('status', [], Manager::EXIT_STATUS_DOWN);

        $this->callMigrate('run', ['-e', 'main'], 0);

        $this->callMigrate('status', [], 0);

        // ensure up migrations worked
        $this->assertFalse($adapter->hasTable('info'));
        $this->assertTrue($adapter->hasTable('statuses'));
        $this->assertTrue($adapter->hasTable('users'));
        $this->assertTrue($adapter->hasTable('just_logins'));
        $this->assertFalse($adapter->hasTable('user_logins'));
        $this->assertTrue($adapter->hasColumn('users', 'biography'));
        $this->assertTrue($adapter->hasForeignKey('just_logins', ['user_id']));
        $this->assertTrue($adapter->hasTable('change_direction_test'));
        $this->assertTrue($adapter->hasColumn('change_direction_test', 'subthing'));
        $this->assertEquals(
            count($adapter->fetchAll('SELECT * FROM change_direction_test WHERE subthing IS NOT NULL')),
            2
        );

        // revert all changes to the first
        $this->callMigrate('rollback', ['-t', '20121213232502'], 0);

        // ensure reversed migrations worked
        $this->assertTrue($adapter->hasTable('info'));
        $this->assertFalse($adapter->hasTable('statuses'));
        $this->assertFalse($adapter->hasTable('user_logins'));
        $this->assertFalse($adapter->hasTable('just_logins'));
        $this->assertTrue($adapter->hasColumn('users', 'bio'));
        $this->assertFalse($adapter->hasForeignKey('user_logins', ['user_id']));
        $this->assertFalse($adapter->hasTable('change_direction_test'));

        // revert all changes to the first
        $this->callMigrate('rollback', ['-t', '0'], 0);

        $this->callMigrate('status', [], Manager::EXIT_STATUS_DOWN);
    }


    /**
     */
    public function testReversibleMigrationWithIndexConflict()
    {
        $this->setPhinxPaths(__DIR__ . '/../_files/drop_index_regression', __DIR__ . '/../_files/empty_seed');

        $adapter = $this->getAdapter();
        $adapter->dropDatabase($adapter->getOption('name'));
        $adapter->createDatabase($adapter->getOption('name'));
        $adapter->disconnect();

        $this->callMigrate('run', []);

        // ensure up migrations worked
        $this->assertTrue($adapter->hasTable('my_table'));
        $this->assertTrue($adapter->hasTable('my_other_table'));
        $this->assertTrue($adapter->hasColumn('my_table', 'entity_id'));
        $this->assertTrue($adapter->hasForeignKey('my_table', ['entity_id']));

        // revert all changes to the first
        $this->callMigrate('rollback', ['-t', '20121213232502']);

        // ensure reversed migrations worked
        $this->assertTrue($adapter->hasTable('my_table'));
        $this->assertTrue($adapter->hasTable('my_other_table'));
        $this->assertTrue($adapter->hasColumn('my_table', 'entity_id'));
        $this->assertFalse($adapter->hasForeignKey('my_table', ['entity_id']));
        $this->assertFalse($adapter->hasIndex('my_table', ['entity_id']));
    }

    public function testReversibleMigrationsWorkAsExpectedWithNamespace()
    {
        $this->setPhinxPaths([
            'Foo\Bar' => __DIR__ . '/../_files_foo_bar/reversiblemigrations'
        ], __DIR__ . '/../_files/empty_seed');

        $adapter = $this->getAdapter();
        $adapter->dropDatabase($adapter->getOption('name'));
        $adapter->createDatabase($adapter->getOption('name'));
        $adapter->disconnect();

        // migrate to the latest version
        $this->callMigrate('run');

        // ensure up migrations worked
        $this->assertFalse($adapter->hasTable('info_foo_bar'));
        $this->assertTrue($adapter->hasTable('statuses_foo_bar'));
        $this->assertTrue($adapter->hasTable('users_foo_bar'));
        $this->assertTrue($adapter->hasTable('user_logins_foo_bar'));
        $this->assertTrue($adapter->hasColumn('users_foo_bar', 'biography'));
        $this->assertTrue($adapter->hasForeignKey('user_logins_foo_bar', ['user_id']));

        // revert all changes to the first
        $this->callMigrate('rollback', ['-t', '20161213232502']);

        // ensure reversed migrations worked
        $this->assertTrue($adapter->hasTable('info_foo_bar'));
        $this->assertFalse($adapter->hasTable('statuses_foo_bar'));
        $this->assertFalse($adapter->hasTable('user_logins_foo_bar'));
        $this->assertTrue($adapter->hasColumn('users_foo_bar', 'bio'));
        $this->assertFalse($adapter->hasForeignKey('user_logins_foo_bar', ['user_id']));
    }

    /**
     */
    public function testMigrateSchema()
    {
        $this->setPhinxPaths([
            'TestMigrations' => __DIR__ . '/../_files/schema_definition',
        ], __DIR__ . '/../_files/empty_seed');

        $adapter = $this->getAdapter();
        $adapter->dropDatabase($adapter->getOption('name'));
        $adapter->createDatabase($adapter->getOption('name'));
        $adapter->disconnect();

        $this->callMigrate('run', ['-e', 'main', '-t', '20190125021334'], 0);

        $this->assertTrue($adapter->hasTable('system'));
        $this->assertTrue($adapter->hasPrimaryKey('system', ['label']));
        $this->assertTrue($adapter->hasPrimaryKey('system', ['label']));
        $this->assertEquals(21, \count($adapter->getColumns('system')));
        $this->assertTrue($adapter->hasIndexByName('permission', 'hash'));

        $this->callMigrate('breakpoint', ['-e', 'main', '-t', '20190125021334'], 0);

        $this->callMigrate('run', ['-e', 'main'], 0);

        $this->assertFalse($adapter->hasIndexByName('permission', 'hash'));
        $this->assertTrue($adapter->hasIndexByName('permission', 'name'));
        $column = null;
        foreach ($adapter->getColumns('system') as $column) {
            if ('string' === $column->getName()) {
                break;
            }
        }
        $this->assertTrue(isset($column));
        $this->assertTrue('string' === $column->getName());
        $this->assertTrue(512 === $column->getLimit());

        $this->callMigrate('rollback', ['-t', '0'], 0);

        $this->assertFalse($adapter->hasIndexByName('permission', 'name'));
        $this->assertTrue($adapter->hasTable('system'));

        $this->callMigrate('breakpoint', ['-e', 'main', '-t', '20190125021334', '--unset'], 0);
        $this->callMigrate('rollback', ['-t', '0'], 0);

        $this->assertFalse($adapter->hasTable('system'));
    }

    /**
     */
    public function testSeeds()
    {
        $this->setPhinxPaths([
            'TestSeedTests' => __DIR__ . '/../_files/seeds_test_migration',
        ], [
            'TestSeedTests' => __DIR__ . '/../_files/seeds_test'
        ]);

        $adapter = $this->getAdapter();
        $adapter->dropDatabase($adapter->getOption('name'));
        $adapter->createDatabase($adapter->getOption('name'));
        $adapter->disconnect();

        $this->callMigrate('run');
        $this->callSeed('run');
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $successCode
     * @param string $driver
     */
    public function callMigrate(string $name, array $parameters = [], $successCode = 0, string $driver = 'console')
    {
        $this->call('migrate:' . $name, $parameters, $exitCode, $driver);
        $this->assertEquals($successCode, $exitCode, "call migrate:{$name} fail");
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $successCode
     * @param string $driver
     */
    public function callSeed(string $name, array $parameters = [], $successCode = 0, string $driver = 'console')
    {
        $this->call('seed:' . $name, $parameters, $exitCode, $driver);
        $this->assertEquals($successCode, $exitCode, "call seed:{$name} fail");
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
