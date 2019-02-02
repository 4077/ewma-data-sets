<?php namespace ewma\dataSets\controllers\main\sets;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function select()
    {
        if ($set = $this->unpackModel('set')) {
            $this->s('~:selected_set_id_by_cat_id/' . $set->cat_id, $set->id, RA);

            $this->e('ewma/dataSets/select')->trigger();
        }
    }

    public function create()
    {
        if ($cat = $this->unxpackModel('cat')) {
            $newSet = $cat->sets()->create([]);

            $this->s('~:selected_set_id_by_cat_id/' . $cat->id, $newSet->id, RA);

            dataSets()->updatePaths($cat);

            $this->e('ewma/dataSets/create')->trigger();
        }
    }

    public function duplicate()
    {
        if ($set = $this->unxpackModel('set')) {
            \ewma\dataSets\models\Set::create($set->toArray());

            $this->e('ewma/dataSets/create')->trigger();
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/dataSets');
        } else {
            if ($set = $this->unxpackModel('set')) {
                if ($this->data('confirmed')) {
                    $set->delete();

                    $this->e('ewma/dataSets/delete', ['set_id' => $set->id])->trigger();

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/dataSets');
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/dataSets', [
                        'path'            => '\std dialogs/confirm~:view',
                        'data'            => [
                            'confirm_call' => $this->_abs(':delete', ['set' => xpack_model($set)]),
                            'discard_call' => $this->_abs(':delete', ['set' => xpack_model($set)]),
                            'message'      => 'Удалить набор <b>' . $set->path . '</b>?'
                        ],
                        'forgot_on_close' => true,
                        'pluginOptions'   => [
                            'resizable' => 'false'
                        ]
                    ]);
                }
            }
        }
    }

    public function rearrange()
    {
        foreach ((array)$this->data('sequence') as $n => $id) {
            if ($group = \ewma\dataSets\models\Set::find($id)) {
                $group->position = (int)$n * 10;
                $group->save();
            }
        }
    }

    public function rename()
    {
        if ($set = $this->unpackModel('set')) {
            $txt = \std\ui\Txt::value($this);

            $set->name = $txt->value;
            $set->path = $set->cat->path . ':' . $set->name;
            $set->save();

            $txt->response();
        }
    }

    public function moveField()
    {
        $targetSet = \ewma\dataSets\models\Set::find($this->data('target_id'));

        list($sourceSetId, $sourceFieldNumber) = explode(':', $this->data('source_id'));

        $sourceSet = \ewma\dataSets\models\Set::find($sourceSetId);

        if ($targetSet && $sourceSet) {
            $targetSetData = _j($targetSet->data);
            $sourceSetData = _j($sourceSet->data);

            $targetSetData[] = $sourceSetData[$sourceFieldNumber];
            $sourceSetData = unmap($sourceSetData, $sourceFieldNumber);

            $targetSet->data = j_($targetSetData);
            $targetSet->save();

            $sourceSet->data = j_($sourceSetData);
            $sourceSet->save();

            $this->c('set~:reload');
        }
    }
}
