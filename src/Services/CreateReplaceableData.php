<?php

namespace ForestLynx\FormDB\Services;

use ForestLynx\FormDB\Traits\Makeable;
use ForestLynx\FormDB\Traits\WithReturnedData;
use Illuminate\Support\Collection;

class CreateReplaceableData
{
    use Makeable;
    use WithReturnedData;

    protected array $data;

    public function __construct (
        protected string $namespace,
        protected string $class,
        protected array|Collection $fields,
    ) {
        $this->create();
    }

    protected function create() : void {
        $this->data = [
            '{namespace}' => $this->namespace,
            '{class}' => $this->class,
            '{fields}' => $this->getFields(),
            '{fields_data}' => $this->getFieldsData(),
        ];
    }

    protected function getFields() : string {
        $returnable = \collect($this->fields)->transform(fn (array $column) => "\t#[Rule('{$column['rules']}')]" . \PHP_EOL . "\tpublic ?{$column['type']} \${$column['field']};")->values()->toArray();

        return \implode(\PHP_EOL,$returnable);
    }

    protected function getFieldsData() : string {
        $returnable = \collect($this->fields)->transform(function (array $column){
            $field = "\t\t\tField::make(field: '{$column['field']}', type: '{$column['type']}'";
            $field .= $column['label'] ? ", label: '{$column['label']}'" :", label: ''";
            if ($column['required']){
                $field .= ', required: true';
            }
            if(!empty($column['data'])){
                $field .= ', data: [';
                foreach ($column['data'] as $key => $value) {
                    $field .= "'$value',";
                }
                $field .= ']';
            }
            return "$field)," . \PHP_EOL;
        })->values()->toArray();

        return \implode(\PHP_EOL,$returnable);
    }
}
