<?php

namespace Spatie\TypeScriptTransformer\Tests\Support;

use Spatie\TypeScriptTransformer\Support\Extensions\TypeScriptTransformerExtension;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;

class FakeExtension implements TypeScriptTransformerExtension
{
    public bool $enrichCalled = false;

    public function enrich(TypeScriptTransformerConfigFactory $factory): void
    {
        $this->enrichCalled = true;
    }
}
