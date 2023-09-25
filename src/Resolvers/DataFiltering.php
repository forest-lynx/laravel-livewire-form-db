<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Resolvers;

use ForestLynx\Filterable\Actions\GetFilteringData;
use ForestLynx\Filterable\Enums\OperatorType;
use ForestLynx\Filterable\Exceptions\ValidationFieldException;
use ForestLynx\Filterable\Traits\WithReturnedData;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Type\Integer;

class DataFiltering
{
    use WithReturnedData;


    private array $data = [];
    private array $availableFilters = [];
    private array $fields = [];
    private array $related = [];
    private array $fulltext = [];

    public function __construct(
        private string $modelName,
        private array $filtersFromRequest
    ) {
        $this->filtersFromRequest = undot_recursive($filtersFromRequest);
    }

    public function generate(): void
    {
        $this->availableFilters = GetFilteringData::make($this->modelName)->getData();

        $this->fields = $this->setFields(\array_diff_key(
            $this->filtersFromRequest,
            \array_flip(['related','fulltext'])
        ) ?? [], \array_diff_key(
            $this->availableFilters,
            \array_flip(['related','fulltext'])
        ) ?? []);

        $this->related = $this->setRelated($this->filtersFromRequest['related'] ?? []);
        dd($this);
        $this->setFulltext($this->filtersFromRequest['fulltext'] ?? []);

        foreach ($this->filtersFromRequest as $key => $value) {
            $value = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
            if (Arr::has($availableFilters, $key)) {
                $extract = Arr::get($availableFilters, $key);

                $fulltextKey = \strpos($key, 'fulltext');

                $options = ($fulltextKey || $fulltextKey !== 0)
                        ? $this->formatValueToArray($value, $key)
                        : compact('value');

                $extract = \array_merge($extract, $options);

                $this->data = array_merge($this->data, Arr::prependKeysWith($extract, "{$key}."));
            }
        }
        $this->data = Arr::undot($this->data);
    }

    private function setFields(array $fields, array $available): array
    {
        $ret = [];
        foreach ($fields as $key => $value) {
            $data = [];
            if (\is_int($key)) {
                foreach ($value as $name => $val) {
                    $data = !Arr::has($available, $name) ?: \array_merge(Arr::get($available, $name), $this->formatValueToArray($name, $val));

                    $ret[$key][$name] = $data;
                }
            } else {
                $data = !Arr::has($available, $key) ?: \array_merge(Arr::get($available, $key), $this->formatValueToArray($key, $value));
                $ret[$key] = $data;
            }
        }

        return $ret ?? [];
    }


    private function setRelated(array $related): array
    {
        foreach ($related as $method => $data) {
            $result[$method] = $this->setFields($data, $this->availableFilters['related'][$method]);
        }

        return $result ?? [];
    }

    private function setFulltext(array $fulltext): void
    {
    }

    private function formatValueToArray(string $field, string $value): array
    {
        try {
            $operator = OperatorType::from(\strtok($value, ':'));
        } catch (\Throwable $th) {
            throw ValidationFieldException::create($field, $value);
        }

        switch ($operator) {
            case OperatorType::BETWEEN:
            case OperatorType::NOT_BETWEEN:
                $result = $this->stepTok('&');
                throw_if(empty($result) || count($result) !== 2, ValidationFieldException::create($field, $value));

                $val = implode(" AND ", $result);
                break;
            case OperatorType::IS_NULL:
            case OperatorType::IS_NOT_NULL:
                $val = null;
                break;
            case OperatorType::LIKE:
            case OperatorType::NOT_LIKE:
                $val = '%' . $this->stepTok(PHP_EOL)[0] . '%';
                break;
            default:
                $val = $this->stepTok(PHP_EOL)[0];
                break;
        };

        return [
        'operator' => $operator,
        'value' => $val ?? null,
    //\preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $val),
        ];
    }

    private function stepTok(string $separator): array
    {
        $returned = [];
        $step = true;
        while ($step !== false) {
            $step = \strtok($separator);
            if ($step) {
                $returned[] = $step;
            }
        }
        return $returned;
    }
}
