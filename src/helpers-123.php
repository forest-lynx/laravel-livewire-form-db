<?php

if (! function_exists('clean_string')) {
    /**
     * Очистка строки от символов и приведение ее к нижнему
     * регистру. Если $trim = true удаляются все пробелы
     *
     * @param  string $str
     * @param  bool $trim Для удаления всех пробелов.
     * @return string
     */
    function clean_string(string $str, bool $trim = false): string
    {
        if ($str === null) {
            return '';
        }

        if ($trim) {
            $identifier = \str_replace(' ', '', $str);
        }

        return trim_chars(strtolower($str));
    }
}

if (! function_exists('trim_chars')) {
    /**
     * Удаление из строки символов (', ", [, ])
     *
     * @param  string $str
     * @return string
     */
    function trim_chars(string $str): string
    {
        return str_replace(['`', '"', '[', ']'], '', $str);
    }
}

if (! function_exists('correct_model_name')) {
    /**
     * Приведение строки к имени Модели
     *
     * @param  string $name
     * @return string
     */
    function correct_model_name(string $name): string
    {
        $name = clean_string($name);
        $name = trim_chars($name);
        return (string) str($name)->singular()->camel()->ucfirst();
    }
}

if (! function_exists('trim_double_spaces')) {
    /**
     * Удаление из строки двойных пробелов
     *
     * @param  string $str
     * @return string
     */
    function trim_double_spaces(string $str): string
    {
        return preg_replace('/\s+/', ' ', $str);
    }
}

if (! function_exists('undot_recursive')) {
    /**
     * Рекурсивное приведение массива с точечной нотацией к
     * многомерному массиву
     *
     * @param  array $array
     * @return array
     */
    function undot_recursive(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                // Если ключ числовой, добавляем значение в корневой уровень массива
                $result[] = is_array($value) ?
                undot_recursive($value) : $value;
            } else {
                $keys = explode('.', $key);
                $tempArray = &$result;

                foreach ($keys as $index => $nestedKey) {
                    if ($index === count($keys) - 1) {
                        $tempArray[$nestedKey] = is_array($value) ? undot_recursive($value) : $value;
                    } else {
                        if (!isset($tempArray[$nestedKey])) {
                            $tempArray[$nestedKey] = [];
                        }
                        $tempArray = &$tempArray[$nestedKey];
                    }
                }
            }
        }

        return $result;
    }
}
