<?php namespace ewma\dataSets\schemas;

class Set extends \Schema
{
    public $table = 'ewma_data_sets';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('cat_id')->default(0);
            $table->integer('cat_position')->default(0);
            $table->integer('position')->default(0);
            $table->string('name')->default('');
            $table->string('path')->default('');
            $table->longText('data');
            $table->longText('inputs'); // todo del
        };
    }
}
