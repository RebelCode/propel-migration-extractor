<?php

namespace RebelCode\Migrations;

use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\Migrations\PropelSqlExtractorInterface as I;

/**
 * Abstract functionality for Propel SQL extractors that store the extract SQL into files.
 *
 * @since [*next-version*]
 */
abstract class AbstractFilePropelSqlExtractor extends AbstractPropelSqlExtractor
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _finishExtraction($upQueries, $downQueries, $migration, $code)
    {
        $this->_writeQueriesToFile(I::UP_MIGRATION, $upQueries, $migration, $code);
        $this->_writeQueriesToFile(I::DOWN_MIGRATION, $downQueries, $migration, $code);
    }

    /**
     * Writes the queries to files.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable     $direction The direction of the query. See {@link UP_SQL} and {@link DOWN_SQL}.
     * @param string[]|Stringable[] $queries   The queries to write to files.
     * @param object                $migration The Propel migration instance.
     * @param string|Stringable     $code      The migration code.
     */
    protected function _writeQueriesToFile($direction, $queries, $migration, $code)
    {
        foreach ($queries as $_schema => $_query) {
            $filePath = $this->_getSqlFilePath($direction, $_schema, $_query, $migration, $code);

            if ($filePath !== null) {
                file_put_contents($filePath, $_query);
            }
        }
    }

    /**
     * Retrieves the file name to use for storing the SQL.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $direction The direction of the query. See {@link UP_SQL} and {@link DOWN_SQL}.
     * @param string|Stringable $schema    The ID of the schema that Propel used to generate the SQL.
     * @param string|Stringable $sql       The SQL to be stored in the file.
     * @param object            $migration The Propel migration instance.
     * @param string|Stringable $code      The migration code.
     *
     * @return string|null The file name, or null if a file name could not be determined.
     */
    abstract protected function _getSqlFilePath($direction, $schema, $sql, $migration, $code);
}
