<?php

namespace RebelCode\Migrations\FuncTest;

use InvalidArgumentException;
use RebelCode\Migrations\NormalizeDirectionCapableTrait as TestSubject;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeDirectionCapableTraitTest extends TestCase
{
    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return TestSubject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(TestSubject::class)
                        ->setMethods(
                            array_merge(
                                [
                                    '__',
                                    '_normalizeString',
                                    '_createInvalidArgumentException',
                                ],
                                $methods
                            )
                        );

        $mock = $builder->getMockForTrait();

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

        $this->assertInternalType(
            'object',
            $subject,
            'An instance of the test subject could not be created'
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

        $before = I::UP_MIGRATION;
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

        $before = I::DOWN_MIGRATION;
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

        $expected = I::DOWN_MIGRATION;
        $before = strtoupper(I::DOWN_MIGRATION);
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
}
