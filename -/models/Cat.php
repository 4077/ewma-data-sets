<?php namespace ewma\dataSets\models;

class Cat extends \Model
{
    protected $table = 'ewma_data_sets_cats';

    public function nested()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}

class CatObserver
{
    public function creating($model)
    {
        $position = Cat::max('position') + 10;

        $model->position = $position;
    }
}

Cat::observe(new CatObserver);
