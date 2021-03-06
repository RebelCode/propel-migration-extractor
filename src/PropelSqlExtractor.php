<?php

namespace RebelCode\Migrations;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * A Propel SQL extractor that stores the extracted SQL in files that can be used by RebelCode and ByJg migration
 * libraries.
 *
 * @since [*next-version*]
 */
class PropelSqlExtractor extends AbstractFilePropelSqlExtractor implements PropelSqlExtractorInterface
{
    /*
     * Filters extracted SQL queries to add SQL that simulates Propel migration.
     *
     * @since [*next-version*]
     */
    use MockPropelFilterQueryCapableTrait;

    /*
     * Provides string normalization capabilities.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides migration direction normalization capabilities.
     *
     * @since [*next-version*]
     */
    use NormalizeDirectionCapableTrait;

    /*
     * Provides functionality for creating invalid argument exception instances.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides string translating capabilities.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The directory name for up migrations.
     *
     * @since [*next-version*]
     */
    const DIR_UP = 'up';

    /**
     * The directory name for down migrations.
     *
     * @since [*next-version*]
     */
    const DIR_DOWN = 'down';

    /**
     * The root directory.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $rootDir The absolute path to the root directory where migration SQL files are stored.
     */
    public function __construct($rootDir)
    {
        $this->_setRootDir($rootDir);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function extract($migration)
    {
        return $this->_extract($migration);
    }

    /**
     * Retrieves the root directory where migration SQL files are stored.
     *
     * @since [*next-version*]
     *
     * @return string The absolute path to the root directory.
     */
    protected function _getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * Sets the root directory where migration SQL files are stored.
     *
     * @since [*next-version*]
     *
     * @param string $rootDir The absolute path to the root directory.
     */
    protected function _setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     *
     * This implementation deduces the file path by continuously checking if a `0000n.sql` file exists in a directory,
     * for n > 0 and incrementing n if the file already exists. Once a file is not found for a given n value, the
     * path of that file is returned.
     *
     * The directory where files are scanned, relative to the root directory configured for this instance, is
     * dependent on the DB schema and the direction of the migration as follows: <root>/<schema>/<direction>
     *
     * @since [*next-version*]
     */
    protected function _getSqlFilePath($direction, $schema, $sql, $migration, $code)
    {
        $rootDirectory = rtrim($this->_getRootDir(), '\\/');
        $subDirectory = $this->_getDirectionDirectoryName($direction);
        $fullDirectory = implode(
            DIRECTORY_SEPARATOR,
            [$rootDirectory, $schema, $subDirectory]
        );

        $fileCounter = 0;

        do {
            ++ $fileCounter;

            $filename = $fullDirectory . DIRECTORY_SEPARATOR . sprintf('%1$05d.sql', $fileCounter);
            $fileExists = file_exists($filename);
            $hasCode = $fileExists && strpos(file_get_contents($filename), $code) !== false;
        } while ($fileExists && !$hasCode);

        return $hasCode
            ? null
            : $filename;
    }

    /**
     * Retrieves the directory name for a specific migration direction.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $direction The migration direction.
     *                                     See {@link UP_MIGRATION} and {@link DOWN_UP_MIGRATION}.
     *
     * @return string The directory name.
     */
    protected function _getDirectionDirectoryName($direction)
    {
        $direction = $this->_normalizeDirection($direction);

        return ($direction === static::UP_MIGRATION)
            ? static::DIR_UP
            : static::DIR_DOWN;
    }
}
