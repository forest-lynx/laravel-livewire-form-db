<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Schema\Contracts;

use Illuminate\Support\Collection;

interface SchemaContract
{
    public function generate(): Collection;
    public function regenerate(): static;
}
