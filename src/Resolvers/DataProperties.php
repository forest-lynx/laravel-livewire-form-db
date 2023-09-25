<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Resolvers;

use ForestLynx\Filterable\Schema\Column;
use ForestLynx\Filterable\Schema\Table;
use ForestLynx\Filterable\Traits\WithReturnedData;
use Illuminate\Support\Arr;

class DataProperties
{
    use WithReturnedData;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create(array $properties, array $options, ?Table $schemaTable = null): self
    {
        $data = [];

        [$timestamp,$softdeletes] = $options;

        if (Arr::isList($properties)) {
            $properties = ($timestamp) ? \array_merge($properties, $timestamp) : $properties;

            $properties = ($softdeletes) ? \array_merge($properties, (array)$softdeletes) : $properties;

            if ($schemaTable) {
                $columnInfo = \collect($schemaTable->getColumns())->transform(
                    fn (Column $column) => $column->comment
                );
                $properties = array_intersect_key($columnInfo->toArray(), array_flip($properties));
            } else {
                $properties = \array_fill_keys($properties, '');
            }
        }

        foreach ($properties as $field => $comment) {
            $skip_columns = \config('filterable.skip_columns');
            if (
                \in_array($field, $skip_columns, true)
                || \preg_match("/_id/", $field)
            ) {
                continue;
            }

            $propertiesColumnDb = [];
            if ($schemaTable) {
                $schemaField = $schemaTable->getColumn($field);
                $propertiesColumnDb = [
                    'type' => $schemaField?->type,
                    'length' => $schemaField->length
                ];
            }

            $data[$field] = [
                'field' => $field,
                ...$propertiesColumnDb,
                'comment' => $comment,
            ];
        }

        return new self($data);
    }
}
