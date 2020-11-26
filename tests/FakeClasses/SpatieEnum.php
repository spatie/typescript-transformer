<?php

namespace Spatie\TypeScriptTransformer\Tests\FakeClasses;

use Spatie\Enum\Enum as BaseEnum;

/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived()
 * @typescript
 */
class SpatieEnum extends BaseEnum
{
    protected static function labels(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }
}
