<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Schema;

use ForestLynx\FormDB\Traits\Makeable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Stringable;

class Column implements Arrayable
{
    use Makeable;

    protected string $type;
    protected string $type_input;
    protected array $data;
    protected string $rules;

    public function __construct(
        protected string $field,
        protected bool $primary,
        protected string $type_sql,
        protected string $label,
        protected bool $auto_increment,
        protected bool $required,
        protected bool $foreigen,
        protected bool $indexed,
        protected ?string $relation_table = null,
    ) {
        $this->field = clean_string($this->field);
        $this->parseColumnType();
    }

    private function parseColumnType(): void
    {
        $data = $this->data();

        $this->type = $data['type'];
        $this->type_input = $data['type_input'];
        $this->data = $data['data'] ?? [];
        $this->rules = ($this->required)
            ? 'required|' . $data['rules']
            : $data['rules'];
    }

    protected function data (): array
    {
        $type = \str($this->type_sql);
        return match (true) {
            $type->contains('varchar'),
            $type->contains('char') => [
                'type' => 'string',
                'type_input' => 'text',
                'rules' => 'string|max:' . filter_var($type, FILTER_SANITIZE_NUMBER_INT),
            ],
            $type == 'binary',
            $type == 'blob',
            $type == 'text' => [
                'type' => 'string',
                'type_input' => 'text',
                'rules' => 'string',
            ],
            $type === 'tinyint(1)' => [
                'type' => 'bool',
                'type_input' => 'checkbox',
                'rules' => 'boolean',
            ],
            $type == 'date' => [
                'type' => 'Carbon',
                'type_input' => 'date',
                'rules' => 'date',
            ],
            $type == 'time' => [
                'type' => 'Carbon',
                'type_input' => 'time',
                'rules' => 'date',
            ],
            $type == 'timestamp' => [
                'type' => 'Carbon',
                'type_input' => 'datetime-local',
                'rules' => 'after_or_equal:1970-01-01 00:00:01|before_or_equal:2038-01-19 03:14:07',
            ],
            $type->contains('set'),
            $type->contains('enum') => $this->enumType($type),
            $type->contains('double'),
            $type->contains('decimal'),
            $type->contains('dec'),
            $type->contains('float') => [
                'type' => 'float',
                'type_input' => 'number',
                'rules' => 'numeric|decimal:2,4'
            ],
            /*$type == 'json' => [
                'type' => 'date',
                'type_input' => 'datetime-local',
                'rules' => 'json',
            ],*/
            $type->contains('year') => [
                'type' => 'integer',
                'type_input' => 'number',
                'rules' => 'min:1901|max:2155',
            ],
            $type->contains('int') && $type !== 'tinyint(1)' => $this->integerType($type)
        };
    }

    protected function enumType(Stringable $type): array{
        $data = preg_match_all("/'([^']*)'/", $type->value, $matches);
        return [
            'type' => 'string',
            'type_input' => 'select',
            'data' => $matches[1],
            'rules' => 'in:' . implode(',', $matches[1]),
        ];
    }

    protected function integerType(Stringable $type) : array
    {
        $rules = $this->required ?'required|':'';
        $rules .= 'integer|numeric|';

        $sign = $type->contains('unsigned') ? 'unsigned' : 'signed';
        $typeInt = preg_replace("/\([^)]+\)/", "",$type->before(' unsigned')->value());

        $length = filter_var($type, FILTER_SANITIZE_NUMBER_INT);
        $int_match = $this->integerMatch($typeInt,$sign);
        $rules .= "min:{$int_match[0]}|";
        $rules .= ($length < $int_match[1]) ? "max:{$length}": "max:{$int_match[1]}";
        return [
            'type' => 'int',
            'type_input' => 'number',
            'rules' => $rules,
        ];
    }

    protected function integerMatch(string $type, string $sing) : array
    {
        return match ([$type, $sing]) {
            ['tinyint', 'unsigned'] => ['0','255'],
            ['tinyint', 'signed'] => ['-128','127'],
            ['smallint', 'unsigned'] => ['0','65535'],
            ['smallint', 'signed'] => ['-32768','32767'],
            ['mediumint', 'unsigned'] => ['0','16777215'],
            ['mediumint', 'signed'] => ['-8388608','8388607'],
            ['int', 'unsigned'] => ['0','4294967295'],
            ['int', 'signed'] => ['-2147483648','2147483647'],
            ['bigint', 'unsigned'] => ['0','18446744073709551615'],
            ['bigint', 'signed'] => ['-9223372036854775808','9223372036854775807'],
            default => []
        };

    }

    public function toArray()
    {
        return [
            'field' => $this->field,
            'primary' => $this->primary,
            'auto_increment' => $this->auto_increment,
            'type_sql' => $this->type_sql,
            'type' => $this->type,
            'type_input' => $this->type_input,
            'label' => $this->label,
            'required' => $this->required,
            'foreigen' => $this->foreigen,
            'indexed' => $this->indexed,
            'relation_table' => $this->relation_table,
            'rules' => $this->rules,
            'data' => $this->data,
        ];
    }
}
