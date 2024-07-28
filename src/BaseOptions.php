<?php

namespace blumewas\LaravelOptions;

use blumewas\LaravelOptions\Models\Option;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

abstract class BaseOptions implements Arrayable, Jsonable
{
    /**
     * The option name to type mapping.
     *
     * @var array<string, string>
     */
    private array $options;

    private array $optional = [];

    public function __construct(
    ) {
        $rc = new \ReflectionClass($this);
        $properties = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $prop) {
            $type = $prop->getType();

            if (! $type instanceof \ReflectionNamedType || ! $type->isBuiltin()) {
                // TODO handle non-builtin types
                continue;
            }

            $name = $prop->getName();

            $this->options[$name] = $type->getName();
            $this->optional[$name] = $type->allowsNull();
        }
    }

    /**
     * Get the options group.
     */
    abstract public static function group(): string;

    /**
     * Set the value of an option.
     */
    public function set(string $name, mixed $value): static
    {
        if (! array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("Option $name is not a public property of ".static::class);
        }

        $this->{$name} = $value;

        return $this;
    }

    /**
     * Get the value of an option.
     */
    public function get(string $name): mixed
    {
        if (! array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("Option '$name' is not a public property of ".static::class);
        }

        if (isset($this->{$name})) {
            return $this->{$name};
        }
        // TODO handle default values?

        if ($this->optional[$name]) {
            return null;
        }

        throw new \Exception("Value for '$name' is not set, has no default or is not optional", 1);
    }

    /**
     * The options.
     *
     * @return Collection<Option>
     */
    public function save(): Collection
    {
        // TODO handle default values?
        return collect($this->options)
            ->map(function (string $type, string $name) {
                return Option::updateOrCreate([
                    'group' => static::group(),
                    'name' => $name,
                ], [
                    'payload' => $this->get($name),
                ]);
            });
    }

    /**
     * Load the options from the database.
     */
    public function load(): static
    {
        $optionModels = Option::where('group', static::group())->get();

        if ($optionModels->isEmpty()) {
            // TODO load the default options?
            return $this;
        }

        $optionModels->each(function (Option $option) {
            $this->set($option->name, $option->payload);
        });

        return $this;
    }

    /**
     * Make an instance of the options class from the given value.
     *
     * @param  mixed  $value
     */
    public static function make($value): static
    {
        $optService = app(OptionsService::class);

        $instance = $optService->makeVia($value, static::class);

        // TODO implement own via methods
        // TODO return a default instance if the value is null
        if (! $instance instanceof static) {
            throw new \InvalidArgumentException('Could not make an instance of '.static::class.' from the given value');
        }

        return $instance;
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return collect($this->options)
            ->mapWithKeys(function (string $type, string $name) {
                return [$name => $this->get($name)];
            })
            ->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
