<?php


use Spatie\TypeScriptTransformer\Transformers\PhpDocTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

beforeEach(function () {
    $config = TypeScriptTransformerConfig::create();

    $this->transformer = new PhpDocTransformer($config);
});

it('will replace types with PhpDoc comment', function () {
    /**
     * @property string $date
     * @property string $time
     */
    class phpDoc
    {
    }

    class noDoc
    {
        public string $date;
        public string $time;
    }


    assertNotNull($this->transformer->transform(new ReflectionClass(phpDoc::class), 'Typed'));
    assertNull($this->transformer->transform(new ReflectionClass(noDoc::class), 'Typed'));
});
