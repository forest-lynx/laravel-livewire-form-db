<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Schema;

use Illuminate\Contracts\Support\Arrayable;

class Fulltext implements Arrayable
{
    public function __construct(
        public string $name,
        public array $fields
    ) {
        $this->name = clean_string($this->name);
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'fields' => $this->fields,
        ];
    }
}
