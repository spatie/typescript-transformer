<?php

namespace Spatie\TypeScriptTransformer\Visitor;

enum VisitorOperationType
{
    case Keep;
    case Remove;
    case Replace;
}
