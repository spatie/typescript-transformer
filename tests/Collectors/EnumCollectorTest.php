<?php

use Spatie\TypeScriptTransformer\Transformers\NativeEnumTransformer;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use Spatie\TypeScriptTransformer\Collectors\EnumCollector;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\BackedEnumWithoutAnnotation;

use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
