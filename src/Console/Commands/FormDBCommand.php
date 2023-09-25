<?php

namespace ForestLynx\FormDB\Console\Commands;

use SplFileInfo;
use ReflectionClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use ForestLynx\FormDB\Exceptions\InvalidTableName;
use ForestLynx\FormDB\Schema\Table;
use ForestLynx\FormDB\Services\CreateReplaceableData;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

use function Laravel\Prompts\{error, info, search, text};

class FormDBCommand extends Command
{
    protected $signature = 'livewire:formdb';

    protected $description = 'Построение класса формы Livewire на основе базы данных и модели.';

    protected string $stubsDir = __DIR__ . '/../../../stubs';
    protected $directoryCreate = 'app/Livewire/Forms';

    protected string $modelsPath = '';
    protected string $modelName = '';
    protected string $stabName = 'form';

    public function handle(): int
    {
        $this->modelsPath = \config('formdb.models_path')
                ? \config('formdb.models_path')
                : text(
                    label: 'Укажите путь до моделей',
                    default: 'app\Models',
                    required: true,
                );

        $modelsName = $this->getModelsName();

        $this->modelName = $modelsName[search(
                    label: 'Поиск модель для генерации',
                    options: fn (string $value) => strlen($value) > 0
                        ? $modelsName->filter(fn (string $modelName) => \str_contains(\strtolower($modelName), \strtolower($value)))->toArray()
                        : [],
                    scroll: 10,
                )];

        $reflection = new ReflectionClass(\ucfirst($this->modelsPath) ."\\". $this->modelName);

        $model = $reflection->newInstance();

        try {
            $dbShemaTable = Table::make(name: $model->getTable());

            $replace = CreateReplaceableData::make(
                namespace: $this->confirmNamespace($this->directoryCreate),
                class: $this->modelName . 'Form',
                fields: $dbShemaTable->getColumns(),
            )->getData();

            $this->createStub($this->directoryCreate . "/{$this->modelName}Form.php",$replace);


            info("Класс: ".$this->directoryCreate . "/{$this->modelName}Form.php успешно сформирован");
            return Command::SUCCESS;

        } catch (InvalidTableName $e) {
            error($e->getMessage());
            return Command::INVALID;
        }

    }

    protected function getModelsName() : Collection {

        return \collect(File::allFiles(\base_path(
            $this->confirmPath($this->modelsPath)
        )))
        ->transform(
            function (SplFileInfo $file) {
                return $file->getBasename('.php');
            }
        )->filter();
    }

    protected function confirmPath(string $name): string{
        return \str_replace(["A","\\"], ["a","/"], $name);
    }

    protected function confirmNamespace(string $name): string{
        return \str_replace(["a","/"], ["A","\\"], $name);
    }

    protected function createStub(string $path, array $replace = []): void
    {
        (new Filesystem)->put(
            $path,
            !empty($replace)
            ? $this->replaceStub($replace)
            : $this->getStub()
        );
    }

    protected function getStubsPath(): string
    {
        throw_if(!isset($this->stubsDir), new InvalidArgumentException('Необходимо определить путь к каталогу с шаблонами.'));

        return $this->stubsDir;
    }

    protected function getStub(): string
    {
        return (new Filesystem)->get($this->getStubsPath()."/$this->stabName.stub");
    }

    protected function replaceStub(array $replace): string
    {
        $stub = $this->getStub();

        return str($stub)
            ->replace(array_keys($replace), array_values($replace))
            ->value();
    }
}
