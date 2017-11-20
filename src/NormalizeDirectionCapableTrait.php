<?php

namespace RebelCode\Migrations;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;

/**
 * Common functionality for normalizing migration directions.
 *
 * @since [*next-version*]
 */
trait NormalizeDirectionCapableTrait
{
    /**
     * Normalizes the given direction string or string-like object.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $direction The direction.
     *
     * @return string The normalized direction.
     */
    protected function _normalizeDirection($direction)
    {
        $direction = strtolower($this->_normalizeString($direction));

        if ($direction !== I::UP_MIGRATION && $direction !== I::DOWN_MIGRATION) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a valid migration direction'),
                null,
                null,
                $direction
            );
        }

        return $direction;
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
