<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class TestOptionsHolder extends Model
{
    // use \Blumewas\LaravelOptions\Concerns\HasOptions;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_options_holder';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
