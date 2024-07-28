<?php

namespace blumewas\LaravelOptions\Eloquent\Casts;

use blumewas\LaravelOptions\BaseOptions;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Options implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        // If the value is already an instance of BaseOptions, return it
        if ($value instanceof BaseOptions) {
            return $value;
        }

        if (! method_exists($model, 'getOptionsClass')) {
            throw new \InvalidArgumentException('Model must have a getOptionsClass method to cast the column to options');
        }

        // If the value is a string, decode it
        $value = json_decode($value, true);

        try {
            return $model->getOptionsClass()::make($value);
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        // If the value is already an instance of BaseOptions, return it as a JSON string
        if ($value instanceof \blumewas\LaravelOptions\BaseOptions) {
            return $value->toJson();
        }

        // If the value is an array, return it as a JSON string
        return json_encode($value);
    }
}
