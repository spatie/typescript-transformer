<?php

namespace Spatie\TypeScriptTransformer\References;

use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;

class PhpClassReference extends ClassStringReference implements FilesystemReference
{
    public function __construct(
        public PhpClassNode $phpClassNode,
    ) {
        parent::__construct($phpClassNode->getName());
    }

    public function getFilesystemOriginPath(): string
    {
        return $this->phpClassNode->getFileName();
    }
}
