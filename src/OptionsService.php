<?php

namespace blumewas\LaravelOptions;

use blumewas\LaravelOptions\Casts\OptionsCast;

class OptionsService
{
    protected array $castClasses = [
        Casts\ViaArray::class,
        Casts\ViaObject::class,
        Casts\ViaJson::class,
    ];

    /**
     * The options casts.
     *
     * @var array<string, OptionsCast>
     */
    protected array $casts;

    public function __construct()
    {
        // Collect all the casts
        $this->casts = collect($this->castClasses)
            ->map(fn ($cast) => new $cast)
            ->reduce(function ($carry, $cast) {
                $handles = $cast->handles();
                if (! is_array($handles)) {
                    $handles = [$handles];
                }

                foreach ($handles as $handle) {
                    $carry[$handle] = $cast;
                }

                return $carry;
            }, []);
    }

    /**
     * Make an instance of the options class from the given value.
     *
     * @param  mixed  $value
     * @return null|\blumewas\LaravelOptions\BaseOptions
     */
    public function makeVia($value, string $optionsClass)
    {
        $cast = $this->getCastFor($value);

        // If no cast is found, return null
        if ($cast == null) {
            return null;
        }

        return $cast->make($value, $optionsClass);
    }

    /**
     * Get the cast for the given value.
     *
     * @param  mixed  $value
     */
    protected function getCastFor($value): ?OptionsCast
    {
        $valueType = gettype($value);
        $valueClass = is_object($value) ? get_class($value) : $valueType;

        // Get the cast by the valueClass then by the value type
        $cast = $this->casts[$valueClass] ?? $this->casts[$valueType] ?? null;

        if ($cast) {
            return $cast;
        }

        return null;
    }
}
