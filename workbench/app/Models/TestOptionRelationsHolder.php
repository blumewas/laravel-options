<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class TestOptionRelationsHolder extends Model
{
    use \blumewas\LaravelOptions\Concerns\HasRelatedOptions;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_option_relations_holder';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the options for the model.
     *
     * @return class-string<\blumewas\LaravelOptions\BaseOptions>
     */
    public function getOptionsClass(): string
    {
        return \Workbench\App\Options\TestOptions::class;
    }
}
