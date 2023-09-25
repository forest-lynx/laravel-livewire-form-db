<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Exceptions;

use Exception;

class InvalidOperatorTypeException extends Exception
{
    public static function create(string $field, string $value): self
    {
        return new self("У поля (`$field`) не верно указано значение типа оператора выборки (`$value`).");
    }
}
