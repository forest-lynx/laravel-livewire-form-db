<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Actions;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Storage;
use ForestLynx\FormDB\Traits\Makeable;

class CreatingDataForFiltering
{
    use Makeable;


    public function __construct(
        private array $data,
        private ?string $disk = null
    ) {
        //TODO отлов ошибок
        //INFO подумать возможно необходимо кеширование
        foreach ($data as $key => $dataModel) {
            $modelName = \strtolower(correct_model_name($dataModel['name']));
            unset($dataModel['filterable']);
            $yaml = Yaml::dump($dataModel, 10);
            Storage::disk($this->getDisk())->put("$modelName.yaml", $yaml, 'private');
        }
    }

    protected function getDisk(): string
    {
        return $this->disk ?: config('formdb.disk_name');
    }
}
