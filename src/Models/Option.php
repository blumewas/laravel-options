<?php

namespace Blumewas\LaravelOptions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $group
 * @property string $name
 * @property string $payload
 */
class Option extends Model
{
    public ?string $payloadCast = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group',
        'name',
        'payload',
    ];

    protected function casts()
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'payload' => $this->payloadCast ?? 'string',
        ];
    }
}
