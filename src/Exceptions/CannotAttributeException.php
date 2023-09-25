<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Exceptions;

use Exception;

class CannotAttributeException extends Exception
{
    public static function create(string $attributeClass): self
    {
        $attributeClass = \class_basename($attributeClass);
        return new self("Класс аттрибута `$attributeClass` отсутствует.");
    }
}
