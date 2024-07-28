<?php

use blumewas\LaravelOptions\Models\Option;
use Workbench\App\Models\TestOptionRelationsHolder;

// it('can create an option relation', function () {
//     $holder = TestOptionRelationsHolder::create();

//     $holder->relatedOptions()->create([
//         'name' => 'strOption',
//         'payload' => 'strValue',
//     ]);

//     expect($holder->relatedOptions()->count())->toBe(1);
// });

it('can load an option relation', function () {
    $holder = TestOptionRelationsHolder::create();

    $holder->relatedOptions()->create([
        'name' => 'strOption',
        'payload' => 'strValue',
    ]);

    $holder = TestOptionRelationsHolder::first();

    expect($holder->relatedOptions()->count())->toBe(1)
        ->and($holder->getOption('strOption'))->toBe('strValue');

    $holder->setOption('strOption', 'newValue');
    $holder->save();

    $this->assertDatabaseHas('options', [
        'name' => 'strOption',
        'payload' => 'newValue',
    ]);
});
