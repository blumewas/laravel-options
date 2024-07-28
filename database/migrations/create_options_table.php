<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();

            $table->string('group');
            $table->string('name');

            $table->json('payload');

            $table->timestamps();

            $table->unique(['group', 'name']);
        });
    }
};
