<?php

namespace Spatie\TypeScriptTransformer\References;

interface FilesystemReference
{
    public function getFilesystemOriginPath(): string;
}
