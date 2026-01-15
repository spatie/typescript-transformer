<?php

namespace Spatie\TypeScriptTransformer\TypeScriptNodes;

use Spatie\TypeScriptTransformer\Data\VisitorProfile;

interface TypeScriptVisitableNode
{
    public function visitorProfile(): VisitorProfile;
}
