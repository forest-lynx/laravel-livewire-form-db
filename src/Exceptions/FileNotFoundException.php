<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public static function create(string $file): self
    {
        return new self("Файл `$file` не существует.");
    }
}
