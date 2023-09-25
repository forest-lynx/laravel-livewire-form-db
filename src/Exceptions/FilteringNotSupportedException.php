<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Exceptions;

use Exception;

class FilteringNotSupportedException extends Exception
{
    public static function create(string $model): self
    {
        $model = \class_basename($model);
        return new self("Фильтрация для модели - `$model` не поддерживается.");
    }
}
