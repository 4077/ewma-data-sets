<?php namespace ewma\dataSets\controllers\main;

class App extends \Controller
{
    public function export()
    {
        if ($cat = $this->unpackModel('cat')) {
            return dataSets()->cats->export($cat);
        }
    }

    public function import()
    {
        if ($cat = $this->unpackModel('cat')) {
            dataSets()->cats->import($cat, $this->data('data'), $this->data('skip_first_level'));
        }
    }
}
