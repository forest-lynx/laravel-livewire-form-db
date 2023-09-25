<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Schema;

use Exception;
use ForestLynx\FormDB\Exceptions\InvalidTableName;
use ForestLynx\FormDB\Schema\Contracts\SchemaContract;
use ForestLynx\FormDB\Traits\Makeable;

class Table
{
    use Makeable;

    public string $comment;
    private array $_columns = [];

    public function __construct(
        public string $name
    ) {
        $this->name = clean_string($this->name);

        \throw_if(!$this->name || !\is_string($this->name), InvalidTableName::create($this->name));

        $tableData = app()->make(SchemaContract::class, ['table' => $name])->generate();

        $this->comment = $tableData->get('comment');

        foreach ($tableData->get('columns') as $column_name => $column_info) {
            $skip_columns = \config('formdb.skip_columns');
            if (\in_array($column_name, $skip_columns, true)) {
                continue;
            }
            $this->_columns[$column_name] = $this->addColumn($column_info)->toArray();
        }
    }

    public function getColumns(): array
    {
        return $this->_columns ?? [];
    }

    /**@throws Exception */
    public function getColumn(string $name): Column
    {
        return $this->_columns[$name];
    }

    private function addColumn(array $options): Column
    {
        return Column::make(...$options);
    }
}
