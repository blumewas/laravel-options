<?php

namespace blumewas\LaravelOptions\Concerns;

trait HasOptions
{
    public function initializeHasOptions()
    {
        if (! isset($this->casts[$this->getOptionColumnName()])) {
            $this->casts[$this->getOptionColumnName()] = \blumewas\LaravelOptions\Eloquent\Casts\Options::class;
        }
    }

    /**
     * Get the options for the model.
     *
     * @return class-string<\blumewas\LaravelOptions\BaseOptions>
     */
    abstract public function getOptionsClass(): string;

    protected function getOptionColumnName(): string
    {
        if (property_exists($this, 'optionColumn')) {
            return $this->optionColumn;
        }

        return 'options';
    }

    /**
     * Get the options for the model.
     */
    public function getOption(string $name): mixed
    {
        $options = $this->getAttribute($this->getOptionColumnName());

        try {
            return $options->get($name);
        } catch (\Throwable $th) {
            if ($this->throwIfOptionNotSet($name)) {
                throw $th;
            }

            return null;
        }
    }

    /**
     * Set the value of an option.
     */
    public function setOption(string $name, mixed $value): static
    {
        $column = $this->getOptionColumnName();
        if (! isset($this->attributes[$column]) || ! is_a($this->attributes[$column], $this->getOptionsClass())) {
            $this->initOptions();
        }

        $options = $this->getAttribute($this->getOptionColumnName());
        $options->set($name, $value);

        $this->setAttribute($this->getOptionColumnName(), $options);

        return $this;
    }

    /**
     * Init the options attribute.
     *
     * @return void
     */
    protected function initOptions()
    {
        if (isset($this->attributes[$this->getOptionColumnName()])) {
            return;
        }

        $this->attributes[$this->getOptionColumnName()] = new ($this->getOptionsClass());
    }

    protected function throwIfOptionNotSet(string $name): bool
    {
        if (property_exists($this, 'throwIfOptionNotSet')) {
            return $this->throwIfOptionsNotSet;
        }

        return false;
    }
}
