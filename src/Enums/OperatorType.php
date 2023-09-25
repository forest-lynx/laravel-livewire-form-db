<?php

namespace ForestLynx\Filterable\Enums;

enum OperatorType: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUAL = '<=';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LIKE = '~';
    case NOT_LIKE = '!~';
    case IN = 'i';
    case NOT_IN = '!i';
    case BETWEEN = '><';
    case NOT_BETWEEN = '!><';
    case IS_NULL = '0';
    case IS_NOT_NULL = '!0';

    public static function map(OperatorType $type): string
    {
        return match ($type) {
            self::EQUALS => '=',
            self::NOT_EQUALS => '<>',
            self::LESS_THAN => '<',
            self::LESS_THAN_OR_EQUAL => '<=',
            self::GREATER_THAN => '>',
            self::GREATER_THAN_OR_EQUAL => '>=',
            self::LIKE => 'LIKE',
            self::NOT_LIKE => 'NOT LIKE',
            self::IN => 'IN',
            self::NOT_IN => 'NOT_IN',
            self::BETWEEN => 'BETWEEN',
            self::NOT_BETWEEN => 'NOT BETWEEN',
            self::IS_NULL => 'IS NULL',
            self::IS_NOT_NULL => 'IS_NOT_NULL',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::EQUALS => 'Равно',
            self::NOT_EQUALS => 'Не равно',
            self::LESS_THAN => 'Меньше',
            self::LESS_THAN_OR_EQUAL => 'Меньше или равно',
            self::GREATER_THAN => 'Больше',
            self::GREATER_THAN_OR_EQUAL => 'Больше или равно',
            self::LIKE => 'Находится в искомом значении',
            self::NOT_LIKE => 'Исключая находящиеся в искомом значении',
            self::IN => 'Находится ли значения в пределах набора',
            self::NOT_IN => 'За исключением значений находящихся наборе',
            self::BETWEEN => 'Находятся в диапазоне',
            self::NOT_BETWEEN => 'Исключая диапазон',
            self::IS_NULL => 'Нулевое значение',
            self::IS_NOT_NULL => 'Не нулевое значение',
        };
    }
}
