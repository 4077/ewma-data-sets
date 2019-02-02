<?php namespace ewma\dataSets\controllers\main\cats;

class App extends \Controller
{
    public function getQueryBuilder()
    {
        return \ewma\dataSets\models\Cat::orderBy('position');
    }

    public function moveCallback()
    {
        $cat = $this->data['cat'];

        dataSets()->updatePaths($cat);
    }

    public function sortCallback()
    {
        $cat = $this->data['cat'];

        dataSets()->updatePaths($cat);
    }
}
