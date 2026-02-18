<?php

use Spatie\TypeScriptTransformer\Collections\PhpNodeCollection;
use Spatie\TypeScriptTransformer\PhpNodes\PhpClassNode;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\ReadonlyClass;
use Spatie\TypeScriptTransformer\Tests\Fakes\TypesToProvide\SimpleClass;

it('can add and get a node', function () {
    $collection = new PhpNodeCollection();
    $node = PhpClassNode::fromClassString(SimpleClass::class);

    $collection->add($node);

    expect($collection->get(SimpleClass::class))->toBe($node);
    expect($collection->has(SimpleClass::class))->toBeTrue();
    expect($collection)->toHaveCount(1);
});

it('can check if a node exists', function () {
    $collection = new PhpNodeCollection();

    expect($collection->has(SimpleClass::class))->toBeFalse();

    $collection->add(PhpClassNode::fromClassString(SimpleClass::class));

    expect($collection->has(SimpleClass::class))->toBeTrue();
});

it('can remove a node', function () {
    $collection = new PhpNodeCollection();
    $collection->add(PhpClassNode::fromClassString(SimpleClass::class));

    $collection->remove(SimpleClass::class);

    expect($collection->has(SimpleClass::class))->toBeFalse();
    expect($collection)->toHaveCount(0);
});

it('does nothing when removing a non-existent node', function () {
    $collection = new PhpNodeCollection();

    $collection->remove(SimpleClass::class);

    expect($collection)->toHaveCount(0);
});

it('replaces a node with the same FQCN', function () {
    $collection = new PhpNodeCollection();
    $node1 = PhpClassNode::fromClassString(SimpleClass::class);
    $node2 = PhpClassNode::fromClassString(SimpleClass::class);

    $collection->add($node1);
    $collection->add($node2);

    expect($collection)->toHaveCount(1);
    expect($collection->get(SimpleClass::class))->toBe($node2);
});

it('can find a node by file path', function () {
    $collection = new PhpNodeCollection();
    $node = PhpClassNode::fromClassString(SimpleClass::class);

    $collection->add($node);

    $found = $collection->findByFile($node->getFileName());

    expect($found)->toBe($node);
});

it('returns null when finding by non-existent file', function () {
    $collection = new PhpNodeCollection();

    expect($collection->findByFile('/non/existent/path.php'))->toBeNull();
});

it('can remove a node by file path', function () {
    $collection = new PhpNodeCollection();
    $node = PhpClassNode::fromClassString(SimpleClass::class);

    $collection->add($node);

    $collection->removeByFile($node->getFileName());

    expect($collection->has(SimpleClass::class))->toBeFalse();
    expect($collection)->toHaveCount(0);
});

it('does nothing when removing by non-existent file', function () {
    $collection = new PhpNodeCollection();
    $collection->add(PhpClassNode::fromClassString(SimpleClass::class));

    $collection->removeByFile('/non/existent/path.php');

    expect($collection)->toHaveCount(1);
});

it('can find nodes by directory', function () {
    $collection = new PhpNodeCollection();
    $node1 = PhpClassNode::fromClassString(SimpleClass::class);
    $node2 = PhpClassNode::fromClassString(ReadonlyClass::class);

    $collection->add($node1);
    $collection->add($node2);

    $directory = dirname($node1->getFileName());
    $found = iterator_to_array($collection->findByDirectory($directory));

    expect($found)->toHaveCount(2);
});

it('can remove nodes by directory', function () {
    $collection = new PhpNodeCollection();
    $node1 = PhpClassNode::fromClassString(SimpleClass::class);
    $node2 = PhpClassNode::fromClassString(ReadonlyClass::class);

    $collection->add($node1);
    $collection->add($node2);

    $directory = dirname($node1->getFileName());
    $collection->removeByDirectory($directory);

    expect($collection)->toHaveCount(0);
});

it('returns null when getting a non-existent node', function () {
    $collection = new PhpNodeCollection();

    expect($collection->get(SimpleClass::class))->toBeNull();
});

it('can iterate over all nodes', function () {
    $collection = new PhpNodeCollection();
    $node1 = PhpClassNode::fromClassString(SimpleClass::class);
    $node2 = PhpClassNode::fromClassString(ReadonlyClass::class);

    $collection->add($node1);
    $collection->add($node2);

    $nodes = iterator_to_array($collection);

    expect($nodes)->toHaveCount(2);
    expect($nodes[SimpleClass::class])->toBe($node1);
    expect($nodes[ReadonlyClass::class])->toBe($node2);
});

it('can get all nodes as array', function () {
    $collection = new PhpNodeCollection();
    $node = PhpClassNode::fromClassString(SimpleClass::class);

    $collection->add($node);

    $all = $collection->all();

    expect($all)->toHaveCount(1);
    expect($all[SimpleClass::class])->toBe($node);
});
