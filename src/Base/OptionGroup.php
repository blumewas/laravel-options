<?php

namespace Blumewas\LaravelOptions\Base;

use Blumewas\LaravelOptions\Models\Option as ModelsOption;

class OptionGroup
{
    /**
     * The options within this group.
     *
     * @var array<string, Option>
     */
    protected array $options = [];

    public string $groupName;

    public function __construct(
        ?string $groupName = null,
    ) {
        $this->groupName = $groupName ?? strtolower(str_replace('Options', '', class_basename(static::class)));

        // Initialize the group and collect options
        $this->collectOptions();
    }

    /**
     * Get the options value by name.
     */
    public function getOption(string $name): mixed
    {
        $option = $this->options[$name] ?? null;

        if (! $option) {
            // TODO maybe throw?
            return null;
        }

        return $option->get();
    }

    /**
     * Set the value for an option in this group.
     */
    public function setOption(string $name, mixed $value): void
    {
        $option = $this->options[$name] ?? null;

        if (! $option) {
            // TODO maybe throw?
            return;
        }

        $option->set($value);
    }

    /**
     * Delete an option from this group.
     */
    public function deleteOption(string $name): bool
    {
        $option = $this->options[$name] ?? null;

        if (! $option) {
            // TODO maybe throw?
            return false;
        }

        return $option->delete();
    }

    /**
     * Save the option value in this group.
     */
    public function saveOption(string $name, mixed $value): bool
    {

        $option = $this->options[$name] ?? null;
        if (! $option) {
            // TODO maybe throw?
            return false;
        }

        if (! empty($value)) {
            $option->set($value);
        }

        // Save the option
        return $option->save();
    }

    /**
     * Save the group itself.
     */
    public function save(): bool
    {
        $upserts = collect($this->options)
            ->map(fn (Option $option) => $option->toArray())
            ->filter(fn ($item) => ! empty($item['payload']))
            ->toArray();

        ModelsOption::upsert($upserts, uniqueBy: ['group', 'name'], update: ['payload']);

        // Logic to save the group
        return true; // Placeholder for actual implementation
    }

    /**
     * Inject the options into the Laravel config.
     *
     * @param  array<string>  $only
     */
    public function injectIntoConfig(array $only = []): void
    {
        $only = array_map(fn ($item) => "{$this->groupName}.$item", $only);

        $options = collect($this->options)
            ->mapWithKeys(fn (Option $option) => $option->injectIntoConfig(true))
            ->filter(function ($value, $key) use ($only) {
                if (empty($only)) {
                    return ! empty($value);
                }

                return in_array($key, $only, true) && ! empty($value);
            })
            ->toArray();

        if (! empty($options)) {
            config($options);
        }
    }

    /**
     * Load the options for this group from the database.
     */
    public function load(): static
    {
        if (empty($this->options)) {
            // TODO maybe throw?
            return $this;
        }

        $optionModels = ModelsOption::where('group', $this->groupName)->get()->keyBy('name');

        foreach ($optionModels as $name => $optionModel) {
            if (! isset($this->options[$name])) {
                $this->options[$name] = new Option(
                    $this->groupName,
                    $name
                );
            }

            $this->options[$name]->init($optionModel);
        }

        // Logic to load the group
        return $this;
    }

    /**
     * Initialize options based on the properties of the class.
     */
    protected function collectOptions(): void
    {
        $reflection = new \ReflectionClass($this);
        // Collect public properties that are options
        $properties = array_filter($reflection->getProperties(\ReflectionProperty::IS_PUBLIC), function ($prop) {
            return $prop->getDeclaringClass()->getName() === static::class;
        });

        $options = ModelsOption::query()
            ->where('group', $this->groupName)
            ->whereIn('name', array_map(fn ($prop) => $prop->getName(), $properties))
            ->get()
            ->keyBy('name');

        // Initialize options based on the properties
        foreach ($properties as $prop) {
            $name = $prop->getName();
            $type = $prop->getType()->getName();

            $this->options[$name] = (new Option(
                $this->groupName,
                $name,
                cast: $type !== 'string' ? $type : null,
            ))->init(
                $options->get($name)
            );
        }
    }
}
