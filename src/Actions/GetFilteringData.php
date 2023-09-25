<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Actions;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Storage;
use ForestLynx\FormDB\Traits\Makeable;
use ForestLynx\FormDB\Traits\WithReturnedData;
use ForestLynx\FormDB\Exceptions\FileNotFoundException;

class GetFilteringData
{
    use Makeable;
    use WithReturnedData;


    private array $data = [];
    private string $fileName;

    public function __construct(
        private string $modelName,
        private ?string $disk = null,
        private bool $fieldsOnly = false
    ) {

        $this->fileName = \strtolower(correct_model_name($this->modelName)) . ".yaml";

        $this->disk = $this->getDisk();
        //TODO отлов ошибок, проверка существования файла
        $this->hasFile()
            ->createData();
    }

    /**@throws FileNotFoundException */
    private function hasFile(): static
    {
        \throw_if(!Storage::disk($this->disk)->exists($this->fileName), FileNotFoundException::create($this->fileName));

        return $this;
    }

    private function createData(): static
    {
        //TODO отлов ошибок
        $data = Yaml::parse(Storage::disk($this->disk)->get($this->fileName));

        if ($this->fieldsOnly) {
            $this->data = $data['fields'];
            return $this;
        }

        $this->data = [
            ...$data['fields'],
            'fulltext' => $data['fulltext'] ?? null,
        ];
        foreach ($data['related'] ?? [] as $key => $relation) {
            $this->data['related'][$key] = static::make(
                modelName: $relation['related'],
                fieldsOnly: true
            )->getData();
        }

        return $this;
    }

    protected function getDisk(): string
    {
        return $this->disk ?: config('formdb.disk_name');
    }
}
