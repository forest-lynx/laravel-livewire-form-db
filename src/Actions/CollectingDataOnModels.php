<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Actions;

use ForestLynx\FormDB\Exceptions\FileNotFoundException;
use SplFileInfo;
use ReflectionClass;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use ForestLynx\FormDB\Traits\Makeable;
use ForestLynx\FormDB\Resolvers\DataModel;
use ForestLynx\FormDB\Traits\WithReturnedData;
use ForestLynx\FormDB\Exceptions\FilteringNotSupportedException;
use ReflectionException;

class CollectingDataOnModels
{
    use Makeable;
    use WithReturnedData;


    private array $data = [];

    public function __construct(
        private ?string $name = null,
        private ?string $modelsPath = null
    ) {

        $modelsPath = $modelsPath ?: config('formdb.models_path');

        if ($name) {
            $name = correct_model_name($name);
            try {
                $data = DataModel::create(new ReflectionClass($modelsPath . $name))->getData();
                if ($data['filterable']) {
                    $this->data[] = $data;
                }
            } catch (FilteringNotSupportedException $e) {
                //TODO обработка исключений
            } catch (ReflectionException $e) {
                //TODO обработка исключений
            }
        } else {
            $this->data = \collect(File::allFiles(\base_path(\str_replace(["A","\\"], ["a","/"], $modelsPath))))
                ->map(
                    function (SplFileInfo $file) use ($modelsPath) {
                        try {
                            $class = new ReflectionClass($modelsPath . \class_basename($file->getBasename('.php')));

                            if (!$class->isSubclassOf(Model::class)) {
                                return false;
                            }

                            return DataModel::create($class)->getData();
                        } catch (FilteringNotSupportedException $e) {
                            //TODO обработка исключений
                            return null;
                        } catch (ReflectionException $e) {
                            //TODO обработка исключений
                            return null;
                        }
                    }
                )->filter()->values()->toArray();
        }
    }
}
