<?php namespace ewma\dataSets\set\controllers\main;

class App extends \Controller
{
    public function readData()
    {
        if ($set = $this->unpackModel('set')) {
            $setData = _j($set->data);

            return $setData[$this->data('number')]['data']['value']['data'];
        }
    }

    public function writeData()
    {
        if ($set = $this->unpackModel('set')) {
            $setData = _j($set->data);

            $setData[$this->data('number')]['data']['value']['data'] = $this->data('data');

            $set->data = j_($setData);
            $set->save();
        }
    }

    public function setCat()
    {
        $cat = \ewma\dataSets\models\Cat::find($this->data('cat_id'));
        $set = \ewma\dataSets\models\Set::find($this->data('set_id'));

        if ($cat && $set) {
            $catIdBefore = $set->cat_id;

            $set->cat()->associate($cat);
            $set->save();

            $this->e('ewma/dataSets/sets/update/cat', [
                'set_id' => $set->id,
                'cat_id' => $catIdBefore
            ])->trigger(['set' => $set]);
        }
    }
}
