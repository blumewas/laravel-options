<?php

namespace Blumewas\LaravelOptions\Base;

use Blumewas\LaravelOptions\Models\Option as ModelsOption;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

class Option implements Arrayable
{
    use HasAttributes;

    /**
     * The instance of the option model.
     */
    protected ?ModelsOption $instance = null;

    private $value = null;

    public function __construct(
        public string $group,
        public string $name,
        protected ?string $defaultValue = null,
        protected ?string $cast = null,
        protected ?string $configKey = null,
    ) {
        // Initialize the option instance
        $this->value = $this->defaultValue;
    }

    /**
     * Get the value of the option
     */
    public function get(): mixed
    {
        $payload = $this->load()->payload;

        if (empty($payload)) {
            return $this->value;
        }

        // Apply the cast if defined
        $this->value = $payload;

        return $this->value;
    }

    /**
     * Set the value of the option.
     */
    public function set(mixed $value): static
    {
        // Set the value in the instance
        $this->value = $value;

        if (! isset($this->instance)) {
            $this->setEmptyInstance();
        }

        // Set the value in the instance
        $this->instance->payload = $value;

        // Logic to set the option value
        return $this;
    }

    /**
     * Save the value of the option to the database
     */
    public function save(): bool
    {
        if (! $this->get()) {
            return false;
        }

        return $this->load()->save();
    }

    /**
     * Delete the option from the database.
     */
    public function delete(): bool
    {
        $this->value = $this->defaultValue;

        if (isset($this->instance)) {
            $result = $this->instance->delete();

            // reset the properties
            $this->setEmptyInstance();

            return $result;
        }

        return false;
    }

    /**
     * Load the option from the database.
     *
     * @throws \Exception
     */
    public function load(): ModelsOption
    {
        if (empty($this->group) || empty($this->name)) {
            throw new \Exception('Group and name must be set before saving the option.');
        }

        if (isset($this->instance)) {
            return $this->instance;
        }

        $instance = (ModelsOption::query()
            ->where('group', $this->group)
            ->where('name', $this->name)
            ->first() ?? new ModelsOption([
                'group' => $this->group,
                'name' => $this->name,
            ]));

        $instance->payloadCast = $this->cast;

        return $this->instance = $instance;
    }

    /**
     * Initialize the option with an existing instance.
     */
    public function init(?ModelsOption $instance): static
    {
        if ($instance === null) {
            return $this;
        }

        $this->instance = $instance;
        $instance->payloadCast = $this->cast;

        return $this;
    }

    /**
     * Convert the option to an array.
     */
    public function toArray(): array
    {
        return [
            'group' => $this->group,
            'name' => $this->name,
            'payload' => $this->get(),
        ];
    }

    /**
     * Inject the option value into the config.
     *
     * @param  bool  $dryrun  If true, only return the value without setting it in the config
     * @return array<string, mixed> The value to be set in the config or null if no config key is set
     */
    public function injectIntoConfig(bool $dryrun = false): array
    {
        $configKey = $this->configKey ?? $this->group.'.'.$this->name;
        $value = $this->get();

        $conf = [
            $configKey => $value,
        ];

        if ($dryrun) {
            return $conf;
        }

        // Set the value in the config
        config($conf);

        return $conf;
    }

    /**
     * Set an empty instance of the option model.
     */
    private function setEmptyInstance(): void
    {
        $instance = new ModelsOption([
            'group' => $this->group,
            'name' => $this->name,
        ]);
        $instance->payloadCast = $this->cast;

        $this->instance = $instance;
    }
}
