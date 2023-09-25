<?php

namespace ForestLynx\FormDB;

use Livewire\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;

abstract class FormDB extends Form
{
    #[Locked]
    abstract public function skippedProperties(): array;
    #[Locked]
    abstract public function getInfoFields(): array;
    #[Locked]
    abstract public function formFields(): Collection;
    #[Locked]
    abstract public function setForm(Model $model): void;

}
