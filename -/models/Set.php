<?php namespace ewma\dataSets\models;

class Set extends \Model
{
    protected $table = 'ewma_data_sets';

    public function cat()
    {
        return $this->belongsTo(Cat::class);
    }
}

class SetObserver
{
    public function creating($model)
    {
        $position = Set::max('position') + 10;

        $model->position = $position;
    }
}

Set::observe(new SetObserver);
