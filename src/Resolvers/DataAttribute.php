<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Resolvers;

use ReflectionClass;
use ReflectionProperty;
use ReflectionAttribute;
use Illuminate\Support\Collection;
use ForestLynx\Filterable\Traits\WithReturnedData;

class DataAttribute
{
    use WithReturnedData;

    public function __construct(public Collection $data)
    {
    }

    public static function create(ReflectionAttribute $attribute): self
    {
        $atInstance = $attribute->newInstance();
        $reflection = new ReflectionClass($attribute->getName());

        $data = \collect($reflection->getProperties())->filter(
            fn (ReflectionProperty $property) => $property->isPublic()
        )
        ->transform(
            fn (ReflectionProperty $property) =>[
                $property->getName() => $property->getValue($atInstance)
            ]
        )->collapse();


        return new self($data);
    }
}
