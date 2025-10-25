<?php

namespace Blumewas\LaravelOptions;

use Blumewas\LaravelOptions\Base\Option;
use Blumewas\LaravelOptions\Base\OptionGroup;

class OptionsService
{
    /**
     * The option groups.
     *
     * @var array<string, OptionGroup>
     */
    private $groups = [];

    public function __construct() {}

    public function group(string $group): OptionGroup
    {
        if (isset($this->groups[$group])) {
            return $this->groups[$group];
        }

        // Create a new OptionGroup if it doesn't exist
        $this->groups[$group] = new OptionGroup($group);

        // Logic to retrieve or create an option group
        return $this->groups[$group];
    }

    /**
     * Retrieve an option by group and name.
     */
    public function get(string $group, string $name): Option
    {
        // Load the option from the database or cache
        $option = new Option(
            $group,
            $name
        );

        $option->load();

        return $option;
    }
}
