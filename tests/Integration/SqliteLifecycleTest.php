<?php
declare(strict_types=1);

namespace Test\Phinx\Integration;

use Phinx\Config\Config;
use Phinx\Migration\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class SqliteLifecycleTest extends TestCase
{
    public function testMigrationSeedAndRollbackWithOrdinaryComposerDependencies(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is required.');
        }
        $directory = sys_get_temp_dir() . '/think-phinx-' . bin2hex(random_bytes(8));
        mkdir($directory . '/migrations', 0700, true);
        mkdir($directory . '/seeds', 0700);
        file_put_contents($directory . '/migrations/20260101000000_create_upgrade_records.php', <<<'MIGRATION'
<?php
use Phinx\Migration\AbstractMigration;
final class CreateUpgradeRecords extends AbstractMigration
{
    public function change(): void
    {
        $this->table('upgrade_records')->addColumn('name', 'string')->create();
    }
}
MIGRATION
        );
        file_put_contents($directory . '/seeds/UpgradeRecordsSeed.php', <<<'SEED'
<?php
use Phinx\Seed\AbstractSeed;
final class UpgradeRecordsSeed extends AbstractSeed
{
    public function run(): void
    {
        $this->table('upgrade_records')->insert([['name' => 'Phinx 0.16.12']])->saveData();
    }
}
SEED
        );
        $manager = new Manager(new Config([
            'paths' => ['migrations' => [$directory . '/migrations'], 'seeds' => [$directory . '/seeds']],
            'environments' => [
                'default_environment' => 'test',
                'default_migration_table' => 'phinxlog',
                'test' => ['adapter' => 'sqlite', 'name' => $directory . '/database'],
            ],
        ]), new ArrayInput([]), new NullOutput());
        $adapter = null;
        try {
            $manager->migrate('test');
            $adapter = $manager->getEnvironment('test')->getAdapter();
            $this->assertTrue($adapter->hasTable('upgrade_records'));
            $this->assertCount(1, $adapter->getVersions());
            $manager->seed('test');
            $this->assertSame('Phinx 0.16.12', $adapter->fetchRow('SELECT name FROM upgrade_records')['name']);
            $manager->rollback('test', 0);
            $this->assertFalse($adapter->hasTable('upgrade_records'));
            $this->assertSame([], $adapter->getVersions());
        } finally {
            if ($adapter !== null) {
                $adapter->disconnect();
            }
            foreach (glob($directory . '/migrations/*') as $file) {
                unlink($file);
            }
            foreach (glob($directory . '/seeds/*') as $file) {
                unlink($file);
            }
            rmdir($directory . '/migrations');
            rmdir($directory . '/seeds');
            foreach (glob($directory . '/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}
