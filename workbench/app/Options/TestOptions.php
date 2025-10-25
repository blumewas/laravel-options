<?php

namespace Workbench\App\Options;

use Blumewas\LaravelOptions\Base\OptionGroup;

class TestOptions extends OptionGroup
{
    public string $foo;

    public int $intOption;
}
