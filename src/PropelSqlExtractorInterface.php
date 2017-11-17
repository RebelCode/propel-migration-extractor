<?php

namespace RebelCode\Migrations;

/**
 * Something that can extract migration SQL from a Propel migration instance.
 *
 * @since [*next-version*]
 */
interface PropelSqlExtractorInterface
{
    /**
     * The identifier used to identify migrations with an "up" direction.
     *
     * @since [*next-version*]
     */
    const UP_MIGRATION = 'up';

    /**
     * The identifier used to identify migrations with an "down" direction.
     *
     * @since [*next-version*]
     */
    const DOWN_MIGRATION = 'down';

    /**
     * Extracts the migration SQL from a Propel migration instance.
     *
     * @since [*next-version*]
     *
     * @param object $migration The Propel migration instance.
     *
     * @return array An array with keys {@link UP_MIGRATION} and {@link DOWN_MIGRATION} that map to sub-arrays.
     *               Each sub-array maps DB schema identifier keys to migration SQL string values.
     */
    public function extract($migration);
}
