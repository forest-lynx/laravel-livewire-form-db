<?php

declare(strict_types=1);

namespace ForestLynx\Filterable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class CommentAttribute
{
    public function __construct(public string $comment)
    {
    }
}
