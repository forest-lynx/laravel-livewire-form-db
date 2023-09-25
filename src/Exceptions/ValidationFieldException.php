<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Exceptions;

use Exception;

class ValidationFieldException extends Exception
{
    public static function create(string $field, string $value): self
    {
        return new self("У поля (`$field`) не верно указано значение  (`$value`).");
    }
}
