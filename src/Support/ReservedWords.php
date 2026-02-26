<?php

namespace Spatie\TypeScriptTransformer\Support;

class ReservedWords
{
    public static function isReserved(string $word): bool
    {
        return $word === 'break'
            || $word === 'case'
            || $word === 'catch'
            || $word === 'class'
            || $word === 'const'
            || $word === 'continue'
            || $word === 'debugger'
            || $word === 'default'
            || $word === 'delete'
            || $word === 'do'
            || $word === 'else'
            || $word === 'enum'
            || $word === 'export'
            || $word === 'extends'
            || $word === 'false'
            || $word === 'finally'
            || $word === 'for'
            || $word === 'function'
            || $word === 'if'
            || $word === 'import'
            || $word === 'in'
            || $word === 'instanceof'
            || $word === 'new'
            || $word === 'null'
            || $word === 'return'
            || $word === 'super'
            || $word === 'switch'
            || $word === 'this'
            || $word === 'throw'
            || $word === 'true'
            || $word === 'try'
            || $word === 'typeof'
            || $word === 'var'
            || $word === 'void'
            || $word === 'while'
            || $word === 'with'
            || $word === 'yield'
            || $word === 'implements'
            || $word === 'interface'
            || $word === 'let'
            || $word === 'package'
            || $word === 'private'
            || $word === 'protected'
            || $word === 'public'
            || $word === 'static';
    }
}
