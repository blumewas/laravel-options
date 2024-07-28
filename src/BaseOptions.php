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

    final public function __construct(
    ) {
        $rc = new \ReflectionClass($this);
        $properties = $rc->getProperties(\ReflectionProperty::IS_PUBLIC);

        $defaults = $this->defaultOptions();

        foreach ($properties as $prop) {
            $type = $prop->getType();

            if (! $type instanceof \ReflectionNamedType || ! $type->isBuiltin()) {
                // TODO handle non-builtin types
                continue;
            }

            $name = $prop->getName();

            $this->options[$name] = $type->getName();
            $this->optional[$name] = $type->allowsNull();

            if (! empty($defaults) && ! isset($this->{$name}) && isset($defaults[$name])) {
                $this->{$name} = $defaults[$name];
            }
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

        // This also handles default values
        if (isset($this->{$name})) {
            return $this->{$name};
        }

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
    public static function load(): static
    {
        $optionModels = Option::where('group', static::group())->get();

        // If there are no options in the database, return the instance as is
        if ($optionModels->isEmpty()) {
            return self::default();
        }

        // Create a new instance of the options class and set the values from the database
        $instance = new static;
        $optionModels->each(function (Option $option) use ($instance) {
            $instance->set($option->name, $option->payload);
        });

        return $instance;
    }

    /**
     * Get the default option values.
     *
     * @return array<string, mixed>
     */
    public function defaultOptions(): array
    {
        return [];
    }

    /**
     * Make an instance of the options class from the given value.
     *
     * @param  mixed  $value
     */
    public static function make($value): static
    {
        // Make an instance of the options class from the given value.
        $makerMethod = static::getMaker($value);
        if ($makerMethod) {
            return call_user_func([static::class, $makerMethod], $value);
        }

        $optService = app(OptionsService::class);

        $instance = $optService->makeVia($value, static::class);
        if (! $instance instanceof static) {
            throw new \InvalidArgumentException('Could not make an instance of '.static::class.' from the given value');
        }

        return $instance;
    }

    /**
     * Create a default instance of the options class.
     */
    public static function default(): static
    {
        return new static;
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

    /**
     * Get the maker method for the given value.
     *
     * @param  mixed  $value
     */
    protected static function getMaker($value): ?string
    {
        $makerMethods = static::getMakerMethods();

        $valueType = gettype($value);
        $valueClass = is_object($value) ? get_class($value) : null;

        return collect($makerMethods)
            ->first(function (string $methodName, string $typeClass) use ($valueType, $valueClass) {
                return $valueClass ? $typeClass === $valueClass : $typeClass === $valueType;
            });
    }

    /**
     * Get the maker methods for the options class.
     * This are all static methods that start with 'via' and have one parameter.
     *
     * @return array<string, string>
     */
    protected static function getMakerMethods(): array
    {
        $class = new \ReflectionClass(static::class);
        $staticMethods = $class->getMethods(\ReflectionMethod::IS_STATIC);

        $makerMethods = [];
        foreach ($staticMethods as $method) {
            if (! $method->isPublic() || ! $method->isStatic()) {
                continue;
            }

            $methodName = $method->getName();
            if (! str_starts_with($methodName, 'via')) {
                continue;
            }

            // maker methods should have one parameter
            $parameters = $method->getParameters();
            if (count($parameters) !== 1) {
                continue;
            }

            $type = $parameters[0]->getType();
            if (! $type instanceof \ReflectionNamedType) {
                continue;
            }

            $makerMethods[$type->getName()] = $methodName;
        }

        return $makerMethods;
    }
}
