<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class TestOptionRelationsHolder extends Model
{
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
}
