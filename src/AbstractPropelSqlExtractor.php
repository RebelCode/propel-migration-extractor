<?php

namespace RebelCode\Migrations;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;
use Traversable;

/**
 * Abstract functionality for a propel SQL migration extractor.
 *
 * @since [*next-version*]
 */
abstract class AbstractPropelSqlExtractor
{
    /**
     * Extracts the up and down SQL queries for a propel migration found at a given path.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $migration The path to the propel migration class file.
     *
     * @return array An array containing two keys mapping to sub-arrays of up and down SQL queries, keyed by schema ID.
     *               The keys of the returned array are {@link K_UP_SQL} and {@link K_DOWN_SQL}.
     */
    protected function _extract($migration)
    {
        $code = $this->_getMigrationClassCode(get_class($migration));

        if ($code === null) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a PropelMigration object'),
                null,
                null,
                $migration
            );
        }

        $upSql = $this->_filterMigrationSqlQueries(
            $migration->getUpSql(),
            I::UP_MIGRATION,
            $migration,
            $code
        );

        $downSql = $this->_filterMigrationSqlQueries(
            $migration->getDownSql(),
            I::DOWN_MIGRATION,
            $migration,
            $code
        );

        $this->_finishExtraction($upSql, $downSql, $migration, $code);

        return [
            I::UP_MIGRATION   => $upSql,
            I::DOWN_MIGRATION => $downSql,
        ];
    }

    /**
     * Retrieves the code of the migration class.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $className The migration class name.
     *
     * @return string|null The migration code or null if the code could not be determined.
     */
    protected function _getMigrationClassCode($className)
    {
        $matches = [];

        preg_match('/PropelMigration_([0-9]*)$/', $className, $matches);

        return count($matches) > 1
            ? $matches[1]
            : null;
    }

    /**
     * Filters the migration SQL, potentially make changes or excluding some queries.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[] $sqlArray  An array of SQL queries.
     * @param string|Stringable     $direction The direction of the query, either "up" or "down".
     * @param object                $migration The migration instance.
     * @param string|Stringable     $code      The migration code.
     *
     * @return string[]|Stringable[] The filtered migration queries, keyed by schema ID.
     */
    protected function _filterMigrationSqlQueries($sqlArray, $direction, $migration, $code)
    {
        $result = [];

        foreach ($sqlArray as $_key => $_sql) {
            $_filtered = $this->_filterMigrationSqlQuery($_sql, $direction, $migration, $code);

            if ($_filtered !== null) {
                $result[$_key] = $this->_normalizeString($_filtered);
            }
        }

        return $result;
    }

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
        $direction = $this->_normalizeString($direction);
        $direction = strtolower($direction);

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
     * Filters a migration SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $sql       The SQL query to filter.
     * @param string|Stringable $direction The direction of the query, either "up" or "down".
     * @param object            $migration The migration instance.
     * @param string|Stringable $code      The migration code.
     *
     * @return string|Stringable The filtered SQL query.
     */
    abstract protected function _filterMigrationSqlQuery($sql, $direction, $migration, $code);

    /**
     * Performs final operations with the extracted SQL queries.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $upQueries   The up SQL queries.
     * @param array|Traversable $downQueries The down SQL queries.
     * @param object            $migration   The migration instance.
     * @param string|Stringable $code        The migration code.
     */
    abstract protected function _finishExtraction($upQueries, $downQueries, $migration, $code);

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
