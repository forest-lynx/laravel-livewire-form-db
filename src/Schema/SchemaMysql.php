<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Schema;

use ForestLynx\FormDB\Schema\Contracts\SchemaContract;

class SchemaMysql extends BaseSchema implements SchemaContract
{
    protected function setSql(): string
    {
        return <<<'SQL'
            SELECT
                t.TABLE_NAME AS name,
                t.TABLE_COMMENT AS comment,
                JSON_OBJECTAGG(c.COLUMN_NAME, JSON_OBJECT(
                            'field', c.COLUMN_NAME,
                            'primary', IFNULL(kcu.COLUMN_NAME, '') = c.COLUMN_NAME,
                            'auto_increment', c.EXTRA = 'auto_increment',
                            'type_sql', c.COLUMN_TYPE,
                            'label', c.COLUMN_COMMENT,
                            'required', c.IS_NULLABLE = 'NO',
                            'foreigen', fk.COLUMN_NAME IS NOT NULL,
                            'indexed', IFNULL(st.INDEX_NAME, '') <> '',
                            'relation_table', fk.REFERENCED_TABLE_NAME
                )) AS `columns`
            FROM information_schema.TABLES t
            LEFT JOIN information_schema.COLUMNS c ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
            LEFT JOIN information_schema.KEY_COLUMN_USAGE kcu ON t.TABLE_SCHEMA = kcu.TABLE_SCHEMA AND t.TABLE_NAME = kcu.TABLE_NAME AND kcu.COLUMN_NAME = c.COLUMN_NAME AND kcu.CONSTRAINT_NAME = 'PRIMARY'
            LEFT JOIN information_schema.STATISTICS st ON t.TABLE_SCHEMA = st.TABLE_SCHEMA AND t.TABLE_NAME = st.TABLE_NAME AND st.COLUMN_NAME = c.COLUMN_NAME
            LEFT JOIN (
                SELECT
                    kcu1.TABLE_SCHEMA,
                    kcu1.TABLE_NAME,
                    kcu1.COLUMN_NAME,
                    kcu1.REFERENCED_TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE kcu1
                WHERE kcu1.REFERENCED_TABLE_NAME IS NOT NULL
            ) fk ON t.TABLE_SCHEMA = fk.TABLE_SCHEMA AND t.TABLE_NAME = fk.TABLE_NAME AND fk.COLUMN_NAME = c.COLUMN_NAME
            WHERE
                t.TABLE_SCHEMA = :dbName
                AND t.TABLE_NAME = :table
                AND t.TABLE_TYPE = 'BASE TABLE'
            GROUP BY
                t.TABLE_NAME,
                t.TABLE_COMMENT;
            SQL;


         $this;
    }
}
