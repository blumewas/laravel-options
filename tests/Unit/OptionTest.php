<?php

use Blumewas\LaravelOptions\Base\Option;

it('can retrieve an option', function () {
    $option = new Option(
        'test',
        'strOption'
    );

    // Ensure the option does not exist initially
    expect($option->get())->toBeNull();

    // Set and save the option
    $option->set('Test Value')->save();
    expect($option->get())->toBe('Test Value');

    // Update the option value
    $option->set('New Value')->save();
    expect($option->get())->toBe('New Value');

    $option->delete();
    expect($option->get())->toBeNull();
    expect($option->save())->toBeFalse();
});

it('can be injected into runtime config', function () {
    $option = new Option(
        'foo',
        'bar'
    );

    expect(
        config('foo.bar')
    )->toBeNull()
        ->and($option->get())->toBeNull();

    $option->set('baz')->save();

    // dryrun the injection
    expect($option->injectIntoConfig(true))->toBe([
        'foo.bar' => 'baz',
    ])->and(
        config('foo.bar')
    )->toBeNull();

    // inject into config
    $option->injectIntoConfig();
    expect(config('foo.bar'))->toBe('baz');
});
