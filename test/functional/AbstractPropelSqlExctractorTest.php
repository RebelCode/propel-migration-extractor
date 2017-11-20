<?php

namespace RebelCode\Migrations\FuncTest;

use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Migrations\AbstractPropelSqlExtractor;
use RebelCode\Migrations\PropelSqlExtractorInterface;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see AbstractPropelSqlExtractor}.
 *
 * @since [*next-version*]
 */
class AbstractPropelSqlExctractorTest extends TestCase
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
        $builder = $this->getMockBuilder(AbstractPropelSqlExtractor::class)
                        ->setMethods(
                            array_merge(
                                [
                                    '__',
                                    '_createInvalidArgumentException',
                                    '_normalizeString',
                                    '_filterMigrationSqlQuery',
                                    '_finishExtraction',
                                ],
                                $methods
                            )
                        );

        $mock = $builder->getMockForAbstractClass();
        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function ($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_normalizeString')->willReturnArgument(0);

        return $mock;
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
            AbstractPropelSqlExtractor::class,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the direction normalization method with an up direction.
     *
     * @since [*next-version*]
     */
    public function testNormalizeDirectionUp()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $before = PropelSqlExtractorInterface::UP_MIGRATION;
        $after = $reflect->_normalizeDirection($before);

        $this->assertEquals($before, $after, 'Valid direction should not have changed.');
    }

    /**
     * Tests the direction normalization method with a down direction.
     *
     * @since [*next-version*]
     */
    public function testNormalizeDirectionDown()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $before = PropelSqlExtractorInterface::DOWN_MIGRATION;
        $after = $reflect->_normalizeDirection($before);

        $this->assertEquals($before, $after, 'Valid direction should not have changed.');
    }

    /**
     * Tests the direction normalization method's sanitization.
     *
     * @since [*next-version*]
     */
    public function testNormalizeDirectionSanitized()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expected = PropelSqlExtractorInterface::DOWN_MIGRATION;
        $before = strtoupper(PropelSqlExtractorInterface::DOWN_MIGRATION);
        $after = $reflect->_normalizeDirection($before);

        $this->assertEquals($expected, $after, 'Direction was not sanitized.');
    }

    /**
     * Tests the direction normalization method with an invalid direction.
     *
     * @since [*next-version*]
     */
    public function testNormalizeDirectionInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_normalizeDirection(uniqid('up-'));
    }

    /**
     * Tests the query bulk filter method to assert whether the single filter method is called for each query and
     * whether null filter results are omitted from the final result.
     *
     * @since [*next-version*]
     */
    public function testFilterMigrationSqlQueries()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $direc = uniqid('direction-');
        $migr = new stdClass();
        $code = uniqid('code-');
        $input = [
            $in1 = uniqid('key-') => uniqid('sql-'),
            $in2 = uniqid('key-') => uniqid('sql-'),
            $in3 = uniqid('key-') => uniqid('sql-'),
            $in4 = uniqid('key-') => uniqid('sql-'),
        ];

        $subject->expects($this->exactly(4))
                ->method('_filterMigrationSqlQuery')
                ->withConsecutive(
                    [$input[$in1], $direc, $migr, $code],
                    [$input[$in2], $direc, $migr, $code],
                    [$input[$in3], $direc, $migr, $code],
                    [$input[$in4], $direc, $migr, $code]
                )
                ->willReturnOnConsecutiveCalls(
                    $input[$in1],
                    $input[$in2],
                    null,
                    $input[$in4]
                );

        $output = $reflect->_filterMigrationSqlQueries($input, $direc, $migr, $code);

        $this->assertCount(3, $output, 'Number of filtered queries does not match expected count.');
    }

    /**
     * Tests the migration class code resolver method.
     *
     * @since [*next-version*]
     */
    public function testGetMigrationClassCode()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $code = (string) rand(1000, 9999);
        $className = sprintf('PropelMigration_%s', $code);

        $output = $reflect->_getMigrationClassCode($className);

        $this->assertEquals($code, $output, 'Expected and retrieved code do not match');
    }

    /**
     * Tests the migration class code resolver method with an invalid class name.
     *
     * @since [*next-version*]
     */
    public function testGetMigrationClassCodeInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $code = rand(1000, 9999);
        $className = sprintf('Propel_%s_Migration', $code);

        $output = $reflect->_getMigrationClassCode($className);

        $this->assertNull($output, 'Result is expected to be null.');
    }

    /**
     * Tests the extraction method to assert whether the result contains the up and down queries of the original
     * Propel migration object.
     *
     * @since [*next-version*]
     */
    public function testExtract()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $code = rand(1000, 9999);
        $schema1 = uniqid('schema-');
        $schema2 = uniqid('schema-');

        $upSql = [
            $schema1 => $up1 = uniqid('up-'),
            $schema2 => $up2 = uniqid('up-'),
        ];
        $downSql = [
            $schema1 => $down1 = uniqid('down-'),
            $schema2 => $down2 = uniqid('down-'),
        ];
        $migration = $this->getMockBuilder('stdClass')
                          ->setMockClassName(sprintf('PropelMigration_%s', $code))
                          ->setMethods(['getUpSQL', 'getDownSQL'])
                          ->getMock();

        $migration->method('getUpSQL')->willReturn($upSql);
        $migration->method('getDownSQL')->willReturn($downSql);
        $subject->method('_filterMigrationSqlQuery')->willReturnArgument(0);

        $this->assertEquals(
            [
                PropelSqlExtractorInterface::UP_MIGRATION => $upSql,
                PropelSqlExtractorInterface::DOWN_MIGRATION => $downSql,
            ],
            $reflect->_extract($migration)
        );
    }

    /**
     * Tests the extraction method with an invalid migration object.
     *
     * @since [*next-version*]
     */
    public function testExtractInvalidObject()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $migration = $this->getMockBuilder('stdClass')
                          ->setMockClassName('InvalidObject')
                          ->getMock();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_extract($migration);
    }
}
