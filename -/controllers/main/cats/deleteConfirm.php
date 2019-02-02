<?php namespace ewma\dataSets\controllers\main\cats;

class DeleteConfirm extends \Controller
{
    public function view()
    {
        $v = $this->v();

        /**
         * @var $confirmSet \ewma\Controllers\Set
         * @var $discardSet \ewma\Controllers\Set
         */
        $confirmSet = $this->_call($this->data('confirm_set'));
        $discardSet = $this->_call($this->data('discard_set'));

        $confirmSet->data('confirmed', true);
        $discardSet->data('discarded', true);

        $v->assign([
                       'MESSAGE'        => $this->getMessage(),
                       'CONFIRM_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => $confirmSet->path(),
                           'data'    => $confirmSet->data(),
                           'class'   => 'button red',
                           'content' => 'Удалить'
                       ]),
                       'DISCARD_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => $discardSet->path(),
                           'data'    => $discardSet->data(),
                           'class'   => 'button blue',
                           'content' => 'Отмена'
                       ]),
                   ]);

        $this->css(':\css\std~');

        return $v;
    }

    private function getMessage()
    {
        $message = 'Категория <b>' . $this->data['cat_name'] . '</b> содержит ';

        $tmp = [];

        if ($this->data['nested_cats_count']) {
            $tmp[] = $this->data['nested_cats_count'] . ' подкатегори' . ending($this->data['nested_cats_count'], 'ю', 'и', 'й');

            $tail = 'Все подкатегории будут удалены.';
        }

        if ($this->data['sets_count']) {
            $tmp[] = $this->data['sets_count'] . ' набор' . ending($this->data['sets_count'], '', 'а', 'ов');

            $tail = 'Все наборы будут удалены.';
        }

        if ($this->data['nested_cats_count'] && $this->data['sets_count']) {
            $tail = 'Все подкатегории и наборы будут удалены.';
        }

        if ($tmp) {
            $message .= implode(' и ', $tmp) . '.';
        }

        $message .= '<br>' . $tail;

        return $message;
    }
}
