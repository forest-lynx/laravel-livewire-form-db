<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Schema;

use ForestLynx\FormDB\Exceptions\InvalidTableName;
use ForestLynx\FormDB\Exceptions\TableNotFoundSchema;
use ForestLynx\FormDB\Schema\Contracts\SchemaContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class BaseSchema implements SchemaContract
{
    protected array $skip_tables;
    protected Collection $tableInfo;

    public function __construct(
        private string $table,
        private ?string $dbName = null)
    {
        if(isset($this->tableInfo)  && $table === $this->tableInfo->name){
            return $this->tableInfo;
        }

        $this->dbName ??= \config('database.connections.mysql.database');
        $this->skip_tables = \config('formdb.skip_tables');

        $this->generate();
    }

    abstract protected function setSql(): string;

    public function generate(): Collection
    {
        $tableInfoDb = DB::select($this->setSql(), ['dbName' => $this->dbName, 'table' => $this->table])[0];

        \throw_if(\in_array($tableInfoDb->name, $this->skip_tables),InvalidTableName::create($this->table));

        $this->tableInfo = \collect([
            'name' => $tableInfoDb->name,
            'comment' => $tableInfoDb->comment,
            'columns' => \json_decode($tableInfoDb->columns, true)
        ]);

        return $this->tableInfo;
    }

    public function regenerate(): static
    {
        $this->tableInfo = \collect();
        $this->generate();

        return $this;
    }
}
