<?php

namespace Spatie\TypeScriptTransformer\References;

interface Reference
{
    public function getKey(): string;

    public function humanFriendlyName(): string;
}
