<?php

namespace blumewas\LaravelOptions\Casts;

use blumewas\LaravelOptions\BaseOptions;

class ViaJson extends OptionsCast
{
    /**
     * Make an instance of the options class from the given value if the value is an array.
     *
     * @param  mixed  $value
     * @return object of \blumewas\LaravelOptions\BaseOptions
     */
    public function fill($value, BaseOptions $options)
    {
        $value = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('The given value is not a valid JSON string');
        }

        // create a new instance of the options class
        foreach ($value as $key => $val) {
            $options->set($key, $val);
        }

        return $options;
    }

    /**
     * Get the types and classes that this cast can handle.
     */
    public function handles(): array|string
    {
        return 'string';
    }
}
