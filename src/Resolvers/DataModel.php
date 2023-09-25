<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Resolvers;

use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;
use ForestLynx\Filterable\Schema\Table;
use ForestLynx\Filterable\Traits\HasFilterable;
use ForestLynx\Filterable\Traits\WithReturnedData;
use Illuminate\Database\Eloquent\Relations\Relation;
use ForestLynx\Filterable\Exceptions\FilteringNotSupportedException;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataModel
{
    use WithReturnedData;

    public function __construct(
        public readonly array $data,
    ) {
    }

    public static function create(ReflectionClass $class): self
    {
        $instanceClass = $class->newInstance();

        $getFromDb = \config('filterable.get_from_database');

        $nameTrait = HasFilterable::class;
        $filterableTraits = collect($class->getTraits())
        ->filter(
            fn (ReflectionClass $trait) => is_a($trait->getName(), $nameTrait, true)
        )->first();

        $filterable = $filterableTraits
            ? $class->getMethod('getFiltering')->invoke($instanceClass)
            : false;

        \throw_if(!$filterable, FilteringNotSupportedException::create($class->getName()));

        $schemaTable = $getFromDb ? new Table($instanceClass->getTable()) : null;

        $attributeNamespace = "ForestLynx\\Filterable\\Attributes";


        $attributes = collect($class->getAttributes())
        ->filter(
            fn (ReflectionAttribute $reflectionAttribute) => class_exists($reflectionAttribute->getName()) && (new ReflectionClass($reflectionAttribute->getName()))->getNamespaceName() === $attributeNamespace
        )
        ->map(
            fn (ReflectionAttribute $reflectionAttribute) => DataAttribute::create($reflectionAttribute)->getData()
        )->collapse();

        if (
            ($attributes->isEmpty() || !$attributes->has('comment'))
            && $getFromDb
        ) {
            $attributes = [
            ...$attributes,
            'comment' => $schemaTable->comment
            ];
        }

        $timestamps = $instanceClass->timestamps
            ? [
                $instanceClass->getCreatedAtColumn(), $instanceClass->getUpdatedAtColumn()
            ]
            : false;
        $softDeletes = \in_array(SoftDeletes::class, $class->getTraitNames()) ? $instanceClass->getDeletedAtColumn() : false;
        $options_properties = [
            $timestamps,
            $softDeletes
        ];

        $filteringFields = DataProperties::create(
            $class->getMethod('getFilteringFields')->invoke($instanceClass),
            $options_properties,
            $schemaTable
        )->getData();

        $relation_methods = DataMethods::create(
            $instanceClass,
            collect($class->getMethods())
                ->filter(
                    fn (ReflectionMethod $method) => is_a($method->getReturnType()?->getName(), Relation::class, true) && $method->isPublic()
                )
        )->getData();

        $fulltext = $getFromDb ? [
            'fulltext' => $schemaTable->getFulltextAll()
        ] : [];

        return new self([
        'name' => \class_basename($class->getName()),
        'filterable' => $filterable,
        ...$attributes,
        'fields' => $filteringFields ?? null,
        'related' => $relation_methods ?? null,
        ...$fulltext ?? null,
        ]);
    }
}
