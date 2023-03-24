<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

use Spatie\Enum\Enum as BaseEnum;

/**
 * @method static self Draft()
 * @method static self Published()
 * @method static self Archived()
 * @typescript
 */
class SpatieEnum extends BaseEnum
{
    protected static function values(): array
    {
        return [
            'Draft' => 'draft',
            'Published' => 'published',
            'Archived' => 'archived',
        ];
    }
}
