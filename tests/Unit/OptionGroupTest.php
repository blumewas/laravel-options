<?php

use Workbench\App\Options\TestOptions;

it('can retrieve an option group', function () {

    $group = new TestOptions;

    expect($group->getOption('foo'))->toBeNull();
    $group->setOption('foo', 'bar');

    expect($group->getOption('foo'))->toBe('bar');

    $group = new TestOptions;
    expect($group->getOption('foo'))->toBeNull();
});

it('can save an option group', function () {
    $group = new TestOptions;

    // Ensure the option does not exist initially
    expect($group->getOption('foo'))->toBeNull();

    // Set and save the option
    $group->setOption('foo', 'Test Value');
    expect($group->getOption('foo'))->toBe('Test Value');

    $group->save();

    $group = new TestOptions;
    expect($group->getOption('foo'))->toBe('Test Value');

    // Update the option value
    $group->saveOption('foo', 'New Value');

    $group = new TestOptions;
    expect($group->getOption('foo'))->toBe('New Value');

    $group->deleteOption('foo');

    expect($group->getOption('foo'))->toBeNull();
});

it('can inject an option group into runtime config', function () {
    $group = new TestOptions;

    expect(config('test.foo'))->toBeNull()
        ->and($group->getOption('foo'))->toBeNull();

    $group->injectIntoConfig();

    expect(config('test.foo'))->toBeNull();

    $group->setOption('foo', 'baz');
    $group->injectIntoConfig();

    expect(config('test.foo'))->toBe('baz');
});
