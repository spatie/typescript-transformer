<?php

namespace Spatie\TypeScriptTransformer\TransformedProviders;

use Spatie\TypeScriptTransformer\Actions\DiscoverTypesAction;
use Spatie\TypeScriptTransformer\Actions\LoadPhpClassNodeAction;
use Spatie\TypeScriptTransformer\Actions\ParseUserDefinedTypeAction;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpStanTypeToTypeScriptNodeAction;
use Spatie\TypeScriptTransformer\Actions\TranspilePhpTypeNodeToTypeScriptNodeAction;

class TransformedProviderActions
{
    public function __construct(
        public readonly LoadPhpClassNodeAction $loadPhpClassNodeAction = new LoadPhpClassNodeAction(),
        public readonly DiscoverTypesAction $discoverTypesAction = new DiscoverTypesAction(),
        public readonly TranspilePhpStanTypeToTypeScriptNodeAction $transpilePhpStanTypeToTypeScriptNodeAction = new TranspilePhpStanTypeToTypeScriptNodeAction(),
        public readonly TranspilePhpTypeNodeToTypeScriptNodeAction $transpilePhpTypeNodeToTypeScriptNodeAction = new TranspilePhpTypeNodeToTypeScriptNodeAction(),
        public readonly ParseUserDefinedTypeAction $parseUserDefinedTypeAction = new ParseUserDefinedTypeAction(),
    ) {
    }
}
