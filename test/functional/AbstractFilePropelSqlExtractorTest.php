<?php

namespace RebelCode\Migrations\FuncTest;

use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Migrations\AbstractFilePropelSqlExtractor as TestSubject;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;
use stdClass;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AbstractFilePropelSqlExtractorTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(TestSubject::class)
                        ->setMethods(
                            array_merge(
                                [
                                    '_getSqlFilePath',
                                ],
                                $methods
                            )
                        );

        $mock = $builder->getMockForAbstractClass();

        return $mock;
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
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInstanceOf(
            TestSubject::class,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the finish extraction method to assert whether the internal file writing mechanism is invoked.
     *
     * @since [*next-version*]
     */
    public function testFinishExtraction()
    {
        $subject = $this->createInstance(['_writeQueriesToFile']);
        $reflect = $this->reflect($subject);

        $schema = uniqid('schema-');
        $upQueries = [$schema => uniqid('up-')];
        $downQueries = [$schema => uniqid('up-')];
        $migration = new stdClass();
        $code = rand(1000, 9999);

        $subject->expects($this->exactly(2))
                ->method('_writeQueriesToFile')
                ->withConsecutive(
                    [I::UP_MIGRATION, $upQueries, $migration, $code],
                    [I::DOWN_MIGRATION, $downQueries, $migration, $code]
                );

        $reflect->_finishExtraction($upQueries, $downQueries, $migration, $code);
    }

    /**
     * Tests the query file writing method to assert whether files are written correctly.
     *
     * @since [*next-version*]
     */
    public function testWriteQueriesToFile()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $dirName = uniqid('dir-');
        $schema = uniqid('schema-');
        $direction = uniqid('direction-');
        $queries = [$schema => uniqid('up-')];
        $fullPath = sprintf('vfs://%1$s/%2$s/%3$s.sql', $dirName, $schema, $direction);
        $migration = new stdClass();
        $code = rand(1000, 9999);

        $subject->method('_getSqlFilePath')
                ->willReturn($fullPath);

        $vfs = $this->createVfs($dirName);

        $reflect->_writeQueriesToFile($direction, $queries, $migration, $code);

        $this->assertFileExists($fullPath, 'Query file does not exist.');

        $vfs->unmount();
    }
}
