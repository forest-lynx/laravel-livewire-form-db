<?php

namespace ForestLynx\FormDB;

class Field
{
    public function __construct(
        public string $field,
        public string $label,
        public string $type = 'text',
        public ?bool $required = false,
        public ?bool $disabled = false,
        public ?string $data = '',
        public ?string $optionLabel = '',
        public ?string $optionValue = '',
        public ?string $format = '',
    ) {
    }

    public static function make(...$options): static
    {
        return new static(...$options);
    }
}
