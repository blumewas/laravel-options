<?php

namespace Workbench\App\Options;

use blumewas\LaravelOptions\BaseOptions;

class TestOptions extends BaseOptions
{
    public string $strOption;

    public ?int $intOption;

    public static function group(): string
    {
        return 'test';
    }
}
