<?php

namespace ForestLynx\Filterable\Enums;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

enum RelationshipType: string
{
    case HasOne = HasOne::class;
    case HasMany = HasMany::class;
    case BelongsTo = BelongsTo::class;
    case HasOneThrough = HasOneThrough::class;
    case HasManyThrough = HasManyThrough::class;
    case BelongsToMany = BelongsToMany::class;
    case MorphTo = MorphTo::class;
    case MorphOne = MorphOne::class;
    case MorphMany = MorphMany::class;
    case MorphToMany = MorphToMany::class;

    public function label(): string
    {
        return match ($this) {
            self::HasOne => 'Один к одному',
            self::HasMany => 'Один ок многим',
            self::BelongsTo => 'Обратная связь для один к одному, один ко многим',
            self::HasOneThrough => 'Один к одной через сквозную таблицу',
            self::HasManyThrough => 'Один ко многим через сквозную таблицу',
            self::BelongsToMany => 'Многие ко многим через связующую таблицу',
            self::MorphTo => 'Отношение связующей таблицы для связанных с ней таблиц',
            self::MorphOne => 'Полиморфное отношение один к одному',
            self::MorphMany => 'Полиморфное отношение один ко многим',
            self::MorphToMany => 'Полиморфное отношение многие ко многим'
        };
    }
}
