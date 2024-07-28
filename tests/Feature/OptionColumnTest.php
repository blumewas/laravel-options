<?php

use Workbench\App\Models\TestOptionsHolder;

it('can create a TestoptionsHolder with empty options', function () {
    TestOptionsHolder::create();

    expect(TestOptionsHolder::first()->options)->toBe(null);
});

it('can set and get an option', function () {
    $holder = TestOptionsHolder::create();

    $holder->setOption('strOption', 'string');

    expect($holder->getOption('strOption'))->toBe('string');
});

it('can set an array as options', function ($value) {
    $holder = TestOptionsHolder::create();

    $holder->options = $value;

    $holder->save();

    expect($holder->fresh()->options->strOption)->toBe('string');
})->with([
    fn () => ['strOption' => 'string'],
    fn () => collect(['strOption' => 'string']),
    fn () => collect(['strOption' => 'string'])->toJson(),
    fn () => (object) ['strOption' => 'string'],
]);
