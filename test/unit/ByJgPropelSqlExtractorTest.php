<?php

namespace RebelCode\Migrations\UnitTest;

use RebelCode\Migrations\ByjgPropelSqlExtractor;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Xpmock\TestCase;
use RebelCode\Migrations\ByJgPropelSqlExtractor as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ByJgPropelSqlExtractorTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param string $rootDir The root directory where to store migration SQL files.
     *
     * @return TestSubject
     */
    public function createInstance($rootDir = null)
    {
        return new ByjgPropelSqlExtractor($rootDir);
    }

    /**
     * Creates a virtual file system.
     *
     * @since [*next-version*]
     *
     * @param string $dir The name of the root directory.
     *
     * @return FileSystem The created virtual file system.
     */
    public function createVfs($dir)
    {
        $vfs = FileSystem::factory('vfs://');
        $vfs->mount();
        $vfs->get('/')->add($dir, new Directory());

        return $vfs;
    }

    /**
     * Creates a new mock propel migration.
     *
     * @since [*next-version*]
     *
     * @param string $code    The propel migration code.
     * @param array  $upSql   An array of schema IDs mapping to sub-arrays of up migration SQL strings.
     * @param array  $downSql An array of schema IDs mapping to sub-arrays of down migration SQL strings.
     *
     * @return object The created propel migration.
     */
    public function createPropelMigration($code, $upSql, $downSql)
    {
        $migration = $this->getMockBuilder('stdClass')
                          ->setMockClassName('PropelMigration_' . $code)
                          ->setMethods(['getUpSQL', 'getDownSQL'])
                          ->getMockForAbstractClass();

        $migration->method('getUpSQL')->willReturn($upSql);
        $migration->method('getDownSQL')->willReturn($downSql);

        return $migration;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance('vfs://');

        $this->assertInstanceOf(
            TestSubject::class,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    public function testExtract()
    {
        $this->createVfs('migrations');

        $subject = $this->createInstance('vfs://migrations/');
        $reflect = $this->reflect($subject);

        $code = rand(1000, 9999);
        $schema1 = uniqid('schema-');
        $schema2 = uniqid('schema-');
        $upSql = [
            $schema1 => uniqid('up-'),
            $schema2 => uniqid('up-'),
        ];
        $downSql = [
            $schema1 => uniqid('down-'),
            $schema2 => uniqid('down-'),
        ];

        $migration = $this->createPropelMigration($code, $upSql, $downSql);

        $subject->extract($migration);

        $schema1Dir = sprintf('vfs://migrations/%s/', $schema1);
        $schema2Dir = sprintf('vfs://migrations/%s/', $schema2);

        $this->fileExists($schema1Dir . 'up/00001.sql');
        $this->fileExists($schema1Dir . 'up/00002.sql');

        $this->fileExists($schema2Dir . 'up/00001.sql');
        $this->fileExists($schema2Dir . 'down/00001.sql');
    }
}
