<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Exceptions;

use Exception;

class CannotModelException extends Exception
{
    public static function create(string $model): self
    {
        $model = \class_basename($model);
        return new self("Класс модели `$model` отсутствует.");
    }
}
