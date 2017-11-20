<?php

namespace RebelCode\Migrations;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;

/**
 * Filters SQL queries by adding manual modifications to the propel migration table.
 *
 * By manually making modifications to the propel table, Propel migrations are "mocked".
 *
 * This allows the extracted SQL queries to be used by another migration library, while still allowing Propel to be used
 * as a dev tool for generating SQL from schema diffs. This is only necessary because Propel cannot generate diffs
 * unless the database appears to be migrated to the latest version.
 *
 * @since [*next-version*]
 */
trait MockPropelFilterQueryCapableTrait
{
    /**
     * Filters a migration SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $sql       The SQL query to filter.
     * @param string|Stringable $schema    The DB schema ID.
     * @param string|Stringable $direction The direction of the query, either "up" or "down".
     * @param object            $migration The migration instance.
     * @param string|Stringable $code      The migration code.
     *
     * @return string
     */
    protected function _filterMigrationSqlQuery($sql, $schema, $direction, $migration, $code)
    {
        $direction = $this->_normalizeDirection($direction);

        // Add an SQL comment with the code
        $sql = sprintf('# %s', $code) . PHP_EOL . $sql;

        // Up or down modification to the propel migration table
        $propelMigrationSql = ($direction === I::UP_MIGRATION)
            ? $this->_getUpPropelMigrationMockSql($code)
            : $this->_getDownPropelMigrationMockSql($code);

        $sql = $sql . PHP_EOL . $propelMigrationSql . PHP_EOL;

        return $sql;
    }

    /**
     * Retrieves the mock propel migration SQL for up migrations.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code The code of the migration.
     *
     * @return string The SQL for up propel migrations.
     */
    protected function _getUpPropelMigrationMockSql($code)
    {
        return sprintf('INSERT INTO `propel_migration` (`version`) VALUES (%s);', $code);
    }

    /**
     * Retrieves the mock propel migration SQL for down migrations.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $code The code of the migration.
     *
     * @return string The SQL for down propel migrations.
     */
    protected function _getDownPropelMigrationMockSql($code)
    {
        return sprintf('DELETE FROM `propel_migration` WHERE `version` = %s;', $code);
    }

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

    /**
     * Normalizes the given direction string or string-like object.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $direction The direction.
     *
     * @return string The normalized direction.
     */
    abstract protected function _normalizeDirection($direction);
}
