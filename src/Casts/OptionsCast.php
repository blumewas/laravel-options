<?php

namespace blumewas\LaravelOptions\Casts;

use blumewas\LaravelOptions\BaseOptions;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Stringable;

abstract class OptionsCast implements \JsonSerializable, Arrayable, Jsonable, Stringable
{
    protected ?string $optionsClass = null;

    /**
     * Create a new options cast.
     */
    public function __construct(
        protected ?\blumewas\LaravelOptions\BaseOptions $options = null,
    ) {
        // if optionsClass is not set, but options is, set optionsClass to the class of options
        if (! is_null($this->options)) {
            $this->optionsClass = get_class($this->options);
        }
    }

    /**
     * Fill the options with the given value.
     *
     * @param  mixed  $value
     * @return \blumewas\LaravelOptions\BaseOptions
     */
    abstract public function fill($value, BaseOptions $options);

    /**
     * Get the values that this cast can handle.
     *
     * @return array<string>|string
     */
    abstract public function handles(): array|string;

    /**
     * Make an instance of the options class from the given value.
     *
     * @param  mixed  $value
     * @return object of \blumewas\LaravelOptions\BaseOptions
     */
    public function make($value, ?string $optionsClass = null)
    {
        if (! $this->canHandle($value)) {
            throw new \InvalidArgumentException('This cast cannot handle the given value');
        }

        $clz = $optionsClass ?? $this->optionsClass;

        if (is_null($clz)) {
            throw new \InvalidArgumentException('Options class must be set');
        }

        // create a new instance of the options class
        return $this->fill($value, new $clz);
    }

    /**
     * Check if this cast can be used for the given value.
     *
     * @param  mixed  $value
     */
    public function canHandle($value): bool
    {
        $handles = $this->handles();
        $valueType = gettype($value);
        $valueClass = is_object($value) ? get_class($value) : null;

        if (is_string($handles)) {
            return $valueClass ? is_a($value, $handles) : $handles === $valueType;
        }

        if (! $valueClass) {
            return in_array($valueType, $handles);
        }

        // check if the value is an instance of any of the handles
        foreach ($handles as $handle) {
            if (is_a($value, $handle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the options to a JSON-serializable array.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the options to a JSON string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Convert the options to a JSON string.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the options as an array.
     */
    public function toArray(): array
    {
        return $this->options->toArray();
    }
}
