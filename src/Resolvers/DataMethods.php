<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Resolvers;

use ReflectionClass;
use ReflectionAttribute;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use ForestLynx\Filterable\Traits\WithReturnedData;
use ForestLynx\Filterable\Enums\RelationshipType;

class DataMethods
{
    use WithReturnedData;

    public function __construct(
        public array $data,
    ) {
    }

    public static function create(Model $model, Collection $methods): self
    {
        $attributeNamespace = "ForestLynx\\Filterable\\Attributes";
        $data = [];
        foreach ($methods as $key => $method) {
            $pivotClass = null;
            $withTimestamps = null;
            $pivotColumns = null;
            $morphType = null;
            $typeEnum = RelationshipType::from($method->getReturnType()->getName());

            $instanceMethod = $method->invoke($model->newInstance());

            $related = $instanceMethod->getRelated();

            if (!\method_exists($related, 'getFiltering') || !$related->getFiltering()) {
                continue;
            }

            $relatedName = \class_basename($related);

            if (method_exists($instanceMethod, 'getPivotAccessor')) {
                $pivotClass = $instanceMethod->getPivotClass();
                $pivotClass = is_subclass_of($pivotClass, Pivot::class, true) || is_subclass_of($pivotClass, MorphPivot::class, true) ? \class_basename($pivotClass) : null;

                $withTimestamps = $instanceMethod?->withTimestamps;
                $morphType = !\method_exists($instanceMethod, 'getMorphType') ? null : $instanceMethod?->getMorphType();

                $pivotColumns = !$pivotClass
                ? $instanceMethod->getPivotColumns()
                : null;
            }

            $attributes = \collect($method->getAttributes())
            ->filter(
                fn (ReflectionAttribute $reflectionAttribute) => class_exists($reflectionAttribute->getName()) && (new ReflectionClass($reflectionAttribute->getName()))->getNamespaceName() === $attributeNamespace
            )
            ->map(
                fn (ReflectionAttribute $attribute) => DataAttribute::create($attribute)->getData()
            )
            ->collapse();

            if (isset($attributes['filtering_allowed']) && !$attributes['filtering_allowed']) {
                continue;
            }

            $data[$method->getName()] = [
                'type' => $typeEnum->name,
                'label' => $typeEnum->label(),
                'related' => $relatedName,
                'pivot_class' => $pivotClass,
                'pivot_columns' => $pivotColumns,
                'with_timestamps' => $withTimestamps,
                'morph_type' => $morphType,
                ...$attributes
            ];
        }



        return new self($data);
    }
}
