<?php

use blumewas\LaravelOptions\BaseOptions;
use blumewas\LaravelOptions\Models\Option;
use Workbench\App\Options\TestOptions;

it('can create a options instance', function () {
    $options = new TestOptions;
    expect($options)->toBeInstanceOf(TestOptions::class)
        ->and($options)->toBeInstanceOf(BaseOptions::class);
});

it('can set and get options', function () {
    $options = new TestOptions;
    $options->set('strOption', 'foo');
    $options->set('intOption', 42);

    expect($options->get('strOption'))->toBe('foo');
    expect($options->get('intOption'))->toBe(42);
});

it('throws an exception when trying to get an option that does not exist', function () {
    $options = new TestOptions;
    $options->get('nonExistingOption');
})->throws(InvalidArgumentException::class);

it('throws an exception when trying to set an option that does not exist', function () {
    $options = new TestOptions;
    $options->set('nonExistingOption', 'foo');
})->throws(InvalidArgumentException::class);

it('can save the options to the database', function () {
    $options = (new TestOptions)
        ->set('strOption', 'foo')
        ->set('intOption', 42);

    $options->save();

    expect($options->get('strOption'))->toBe('foo');

    expect(Option::where('group', 'test')->count())->toBe(2);
});

it('can load the options from the database', function () {
    Option::create([
        'group' => 'test',
        'name' => 'strOption',
        'payload' => 'foo',
    ]);

    Option::create([
        'group' => 'test',
        'name' => 'intOption',
        'payload' => 42,
    ]);

    $options = TestOptions::load();

    expect($options->get('strOption'))->toBe('foo');
    expect($options->get('intOption'))->toBe(42);
});
