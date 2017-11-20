<?php

namespace RebelCode\Migrations\FuncTest;

use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Migrations\MockPropelFilterQueryCapableTrait as TestSubject;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class MockPropelFilterQueryCapableTraitTest extends TestCase
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
                                    '__',
                                    '_createInvalidArgumentException',
                                    '_normalizeDirection',
                                ],
                                $methods
                            )
                        );

        $mock = $builder->getMockForTrait(TestSubject::class);

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_normalizeDirection')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );

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

        $this->assertInternalType(
            'object',
            $subject,
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the filter migration sql method with an up direction to assert whether to correctly adds the mock SQL to
     * the original input SQL.
     *
     * @since [*next-version*]
     */
    public function testFilterMigrationSqlQueryUp()
    {
        $subject = $this->createInstance(['_getUpPropelMigrationMockSql', '_getDownPropelMigrationMockSql']);
        $reflect = $this->reflect($subject);

        $direction = I::UP_MIGRATION;
        $originalSql = uniqid('sql-');
        $addedSql = uniqid('added-');
        $migration = new stdClass();
        $code = uniqid('code-');

        $subject->method('_getUpPropelMigrationMockSql')
                ->willReturn($addedSql);

        $output = $reflect->_filterMigrationSqlQuery($originalSql, $direction, $migration, $code);

        $this->assertContains($originalSql, $output);
        $this->assertContains($addedSql, $output);
    }

    /**
     * Tests the filter migration sql method with a down direction to assert whether to correctly adds the mock SQL to
     * the original input SQL.
     *
     * @since [*next-version*]
     */
    public function testFilterMigrationSqlQueryDown()
    {
        $subject = $this->createInstance(['_getUpPropelMigrationMockSql', '_getDownPropelMigrationMockSql']);
        $reflect = $this->reflect($subject);

        $direction = I::DOWN_MIGRATION;
        $originalSql = uniqid('sql-');
        $addedSql = uniqid('added-');
        $migration = new stdClass();
        $code = uniqid('code-');

        $subject->method('_getDownPropelMigrationMockSql')
                ->willReturn($addedSql);

        $output = $reflect->_filterMigrationSqlQuery($originalSql, $direction, $migration, $code);

        $this->assertContains($originalSql, $output);
        $this->assertContains($addedSql, $output);
    }
}
