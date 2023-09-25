<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Exceptions;

use Exception;

class TableNotFoundSchema extends Exception
{
    public static function create(string $name): self
    {
        return new self("Таблица " . (string) $name . " не найдена в схеме базы данных.");
    }
}
