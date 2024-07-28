<?php

namespace blumewas\LaravelOptions\Concerns;

use blumewas\LaravelOptions\BaseOptions;
use blumewas\LaravelOptions\Eloquent\Relations\HasOptions;
use blumewas\LaravelOptions\Models\Option;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasRelatedOptions
{
    /**
     * The options attribute.
     */
    protected BaseOptions $_options;

    public static function bootHasRelatedOptions()
    {
        static::addGlobalScope('options', function (Builder $builder) {
            $builder->with('relatedOptions');
        });

        static::retrieved(function (Model $model) {
            // TODO fix eager loading
            // if (! $model->relationLoaded('relatedOptions') || ! $model->relatedOptions->isEmpty()) {
            //     return;
            // }

            foreach ($model->relatedOptions as $option) {
                $model->_options->set($option->name, $option->payload);
            }
        });

        static::saved(function (Model $model) {
            $model->saveOptions();
        });
    }

    public function initializeHasRelatedOptions()
    {
        if (! isset($this->_options)) {
            $this->_options = new ($this->getOptionsClass());
        }
    }

    public function relatedOptions(): HasOptions
    {
        return $this->hasOptions($this->getOptionsClass());
    }

    /**
     * Get the options for the model.
     *
     * @return class-string<\blumewas\LaravelOptions\BaseOptions>
     */
    abstract public function getOptionsClass(): string;

    /**
     * Get the options for the model.
     */
    public function getOption(string $name): mixed
    {
        $options = $this->_options;

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
        $this->_options->set($name, $value);

        return $this;
    }

    /**
     * Save the current options.
     */
    public function saveOptions(): void
    {
        try {
            $options = collect($this->_options->toArray())
                ->map(function ($payload, $name) {
                    if ($payload === null) {
                        return;
                    }

                    $group = $this->getOptionKey();

                    return compact('name', 'group', 'payload');
                })->filter()->values()->all();

            $this->relatedOptions()->upsert($options, ['name', 'group'], ['payload']);
        } catch (\Throwable $th) {
            //
        }
    }

    protected function throwIfOptionNotSet(string $name): bool
    {
        if (property_exits($this, 'throwIfOptionNotSet')) {
            return $this->throwIfOptionsNotSet;
        }

        return false;
    }

    /**
     * Define a one-to-many relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<BaseOptions>  $optionClass
     */
    public function hasOptions(string $optionClass, $localKey = null)
    {
        $instance = $this->newRelatedInstance(Option::class);

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasOptions(
            $instance->newQuery(), $this, $instance->getTable().'.group', $localKey, $optionClass
        );
    }

    /**
     * Instantiate a new HasMany relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @param  class-string<BaseOptions>  $optionClass
     * @return HasOptions<TRelatedModel, TDeclaringModel>
     */
    protected function newHasOptions(Builder $query, Model $parent, $foreignKey, $localKey, string $optionClass)
    {
        return new HasOptions($query, $parent, $foreignKey, $localKey, $optionClass);
    }

    /**
     * Get the options group key to retrieve related option models.
     */
    public function getOptionKey(): string
    {
        $group = $this->getOptionsClass()::group();
        $keyValue = $this->getAttribute($this->getKeyName());

        return "{$group}-{$keyValue}";
    }
}
