<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class TestOptionsHolder extends Model
{
    use \blumewas\LaravelOptions\Concerns\HasOptions;

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

    public function getOptionsClass(): string
    {
        return \Workbench\App\Options\TestOptions::class;
    }
}
