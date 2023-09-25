<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Exceptions;

use Exception;

class InvalidTableName extends Exception
{
    public static function create(string $name): self
    {
        return new self("Указано недопустимое имя таблицы: " . (string) $name);
    }
}
