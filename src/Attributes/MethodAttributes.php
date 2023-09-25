<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class MethodAttributes
{
    public function __construct(
        public ?string $comment = '',
        public ?bool $filtering_allowed = true
    ) {
    }
}
