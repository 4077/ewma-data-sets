<?php namespace ewma\dataSets\schemas;

class Cat extends \Schema
{
    public $table = 'ewma_data_sets_cats';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('parent_id')->default(0);
            $table->integer('position')->default(0);
            $table->string('name')->default('');
            $table->text('path');
        };
    }
}
