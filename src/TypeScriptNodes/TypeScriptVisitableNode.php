<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Support\VisitorProfile;

interface TypeScriptVisitableNode
{
    public function visitorProfile(): VisitorProfile;
}
