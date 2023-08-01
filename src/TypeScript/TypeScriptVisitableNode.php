<?php

namespace Spatie\TypeScriptTransformer\TypeScript;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;

interface TypeScriptVisitableNode
{
    public function visitorProfile(): VisitorProfile;
}
