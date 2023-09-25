<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Exceptions;

use Exception;

class UnsupportedDbDriver extends Exception
{
    public static function create(string $driver): self
    {
        return new self("Драйвер `$driver` базы данных не поддерживается пакетом.");
    }
}
