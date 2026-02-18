---
title: Node reference
weight: 4
---

A quick reference of all available TypeScript AST nodes.

## Types

### Primitives

| Node | Output |
|------|--------|
| `new TypeScriptString()` | `string` |
| `new TypeScriptNumber()` | `number` |
| `new TypeScriptBoolean()` | `boolean` |
| `new TypeScriptNull()` | `null` |
| `new TypeScriptUndefined()` | `undefined` |
| `new TypeScriptVoid()` | `void` |
| `new TypeScriptNever()` | `never` |
| `new TypeScriptUnknown()` | `unknown` |
| `new TypeScriptAny()` | `any` |

### Combining Types

**TypeScriptUnion** — `string | number`
```php
new TypeScriptUnion([new TypeScriptString(), new TypeScriptNumber()])
```

**TypeScriptIntersection** — `A & B`
```php
new TypeScriptIntersection([new TypeScriptIdentifier('A'), new TypeScriptIdentifier('B')])
```

**TypeScriptArray** — `string[]`
```php
new TypeScriptArray([new TypeScriptString()])
```

**TypeScriptTuple** — `[string, number]`
```php
new TypeScriptTuple([new TypeScriptString(), new TypeScriptNumber()])
```

### Generics

**TypeScriptGeneric** — `Record<string, number>`

Used both for generic type *usage* (concrete type arguments) and generic type *declarations* (with `TypeScriptGenericTypeParameter` arguments).

```php
// Usage: Record<string, number>
new TypeScriptGeneric(new TypeScriptIdentifier('Record'), [new TypeScriptString(), new TypeScriptNumber()])

// Declaration: Container<T extends object>
new TypeScriptGeneric(new TypeScriptIdentifier('Container'), [
    new TypeScriptGenericTypeParameter(new TypeScriptIdentifier('T'), extends: new TypeScriptIdentifier('object')),
])
```

**TypeScriptGenericTypeParameter** — `T`, `T extends string`, `T extends string = string`

Declares a generic type variable with optional constraint and default. Used inside `TypeScriptGeneric` for type declarations.

```php
// Bare: T
new TypeScriptGenericTypeParameter(new TypeScriptIdentifier('T'))

// With constraint: T extends string
new TypeScriptGenericTypeParameter(new TypeScriptIdentifier('T'), extends: new TypeScriptString())

// With constraint and default: T extends string = string
new TypeScriptGenericTypeParameter(new TypeScriptIdentifier('T'), extends: new TypeScriptString(), default: new TypeScriptString())
```

### Advanced Type Operators

**TypeScriptConditional** — `T extends string ? number : boolean`
```php
new TypeScriptConditional(
    TypeScriptOperator::extends(new TypeScriptIdentifier('T'), new TypeScriptString()),
    new TypeScriptNumber(),
    new TypeScriptBoolean(),
)
```

**TypeScriptMappedType** — `{ [K in keyof T]: T[K] }`

```php
// Simple: { [K in keyof T]: T[K] }
new TypeScriptMappedType(
    'K',
    TypeScriptOperator::keyof(new TypeScriptIdentifier('T')),
    new TypeScriptIndexedAccess(new TypeScriptIdentifier('T'), [new TypeScriptIdentifier('K')]),
)

// With modifiers: { readonly [K in keyof T]?: T[K] }
new TypeScriptMappedType(
    'K',
    TypeScriptOperator::keyof(new TypeScriptIdentifier('T')),
    new TypeScriptIndexedAccess(new TypeScriptIdentifier('T'), [new TypeScriptIdentifier('K')]),
    readonlyModifier: 'readonly',
    optionalModifier: '?',
)
```

**TypeScriptIndexedAccess** — `User["name"]`
```php
new TypeScriptIndexedAccess(new TypeScriptIdentifier('User'), [new TypeScriptLiteral('name')])
```

**TypeScriptOperator** — `keyof T`, `typeof config`, `T extends U`
```php
TypeScriptOperator::keyof(new TypeScriptIdentifier('T'))
TypeScriptOperator::typeof(new TypeScriptIdentifier('config'))
TypeScriptOperator::extends(new TypeScriptIdentifier('T'), new TypeScriptIdentifier('U'))
```

**TypeScriptCallable** — `(...args: any[]) => any` or custom function types like `(x: string) => void`
```php
new TypeScriptCallable() // (...args: any[]) => any
new TypeScriptCallable([new TypeScriptParameter('x', new TypeScriptString())], new TypeScriptVoid()) // (x: string) => void
```

## Objects & Interfaces

**TypeScriptObject** — `{ name: string }`

For describing object type shapes in type annotations. Compare with `TypeScriptObjectLiteral` for value-level JSON objects.

```php
new TypeScriptObject([new TypeScriptProperty('name', new TypeScriptString())])
```

**TypeScriptProperty** — `readonly name?: string`
```php
new TypeScriptProperty('name', new TypeScriptString(), isOptional: true, isReadonly: true)
```

**TypeScriptIndexSignature** — `[key: string]`
```php
new TypeScriptIndexSignature(new TypeScriptString(), 'key')
```

**TypeScriptInterface** — `interface User { name: string; greet(): void; }`
```php
new TypeScriptInterface('User', [new TypeScriptProperty('name', new TypeScriptString())], [new TypeScriptMethodSignature('greet', [], new TypeScriptVoid())])
```

**TypeScriptMethodSignature** — `getName(id: number): string;`
```php
new TypeScriptMethodSignature('getName', [new TypeScriptParameter('id', new TypeScriptNumber())], new TypeScriptString())
```

## Declarations & Expressions

### Declarations

**TypeScriptAlias** — `type Name = string;`
```php
new TypeScriptAlias('Name', new TypeScriptString())
// or with explicit identifier: new TypeScriptAlias(new TypeScriptIdentifier('Name'), new TypeScriptString())
```

**TypeScriptEnum** — `enum Status { Active = 'active' }`
```php
new TypeScriptEnum('Status', [['name' => 'Active', 'value' => 'active']])
```

**TypeScriptFunctionDeclaration** — `function greet(name: string): string { ... }`
```php
new TypeScriptFunctionDeclaration('greet', [new TypeScriptParameter('name', new TypeScriptString())], new TypeScriptString(), new TypeScriptRaw('return name;'))
```

**TypeScriptVariableDeclaration** — `const name = "world"`
```php
TypeScriptVariableDeclaration::const('name', new TypeScriptLiteral('world'))
```

**TypeScriptExport** — `export type Name = string;`
```php
new TypeScriptExport(new TypeScriptAlias('Name', new TypeScriptString()))
```

**TypeScriptImport** — `import { User as AppUser } from './types';`
```php
new TypeScriptImport('./types', [['name' => 'User', 'alias' => 'AppUser']])
```

**TypeScriptNamespace** — `declare namespace App { namespace Models { ... } }` or `namespace Models { ... }`
```php
new TypeScriptNamespace('App', [$typeNode], children: [
    new TypeScriptNamespace('Models', [$otherTypeNode], declare: false)
])
```

### Expressions

Value-level nodes that produce JavaScript/TypeScript expressions. Some output similar syntax to type-level nodes but serve a different purpose.

**TypeScriptCallExpression** — `createAction<UserParams>("index")`
```php
new TypeScriptCallExpression(new TypeScriptIdentifier('createAction'), [new TypeScriptLiteral('index')], genericTypes: [new TypeScriptIdentifier('UserParams')])
```

**TypeScriptArrayExpression** — `["a", "b", "c"]`

For array literals in expressions. Compare with `TypeScriptTuple` for type-level tuples.

```php
new TypeScriptArrayExpression([new TypeScriptLiteral('a'), new TypeScriptLiteral('b'), new TypeScriptLiteral('c')])
```

**TypeScriptObjectLiteral** — `{ "method": "GET", "url": "/users" }`

For JSON object values. Compare with `TypeScriptObject` for type-level object shapes.

```php
new TypeScriptObjectLiteral(['method' => 'GET', 'url' => '/users'])
```

## Building Blocks

Low-level nodes used as parts of other nodes.

**TypeScriptIdentifier** — `MyType` (auto-quotes invalid identifiers)
```php
new TypeScriptIdentifier('MyType')
```

**TypeScriptLiteral** — `"hello"`, `42`, `true`
```php
new TypeScriptLiteral('hello')
```

**TypeScriptParameter** — `name?: string`, `...args: string[]`
```php
new TypeScriptParameter('name', new TypeScriptString(), isOptional: true)
```

**TypeScriptRaw** — pass-through raw TypeScript, supports `references` for `%placeholder%` substitution and `additionalImports` for external TS file imports
```php
new TypeScriptRaw('Record<string, never>')
new TypeScriptRaw('%User% | null', references: ['User' => UserData::class])
```
