<?php namespace ewma\dataSets\controllers\main\cats\node;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function select()
    {
        if ($cat = $this->unxpackModel('cat')) {
            $this->s('~:selected_cat_id|', $cat->id, RR);

            $this->e('ewma/dataSets/cat_select')->trigger();
        }
    }

    public function create()
    {
        if ($cat = $this->unxpackModel('cat')) {
            $newCat = dataSets()->cats->create($cat);

            $this->s('~:selected_cat_id|', $newCat->id, RR);

            $this->e('ewma/dataSets/cats/create', ['cat_id' => $cat->id])->trigger(['cat' => $cat]);

            $this->c('~:reload'); // ?
        }
    }

    public function duplicate()
    {
        if ($cat = $this->unxpackModel('cat')) {
            dataSets()->cats->duplicate($cat);

            $this->e('ewma/dataSets/cats/create', ['cat_id' => $cat->id])->trigger(['cat' => $cat]);
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteCatConfirm|ewma/dataSets');
        } else {
            if ($cat = $this->unpackModel('cat')) {
                $catsIds = \ewma\Data\Tree::getIds($cat);

                $nestedCatsCount = count($catsIds) - 1;

                $sets = \ewma\dataSets\models\Set::whereIn('cat_id', $catsIds)->get();
                $setsCount = count($sets);

                if ($this->dataHas('confirmed') || (!$nestedCatsCount && !$setsCount)) {
                    dataSets()->cats->delete($cat);

                    $selectedCatId = &$this->s('~:selected_cat_id|');
                    if (in_array($selectedCatId, $catsIds)) {
                        $selectedCatId = false;
                    }

                    $this->c('~:reload'); // todo event

                    $this->c('\std\ui\dialogs~:close:deleteCatConfirm|ewma/dataSets');
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteCatConfirm|ewma/dataSets', [
                        'path'          => '~cats/deleteConfirm:view',
                        'data'          => [
                            'confirm_set'       => $this->_abs(':delete|', ['cat' => $this->data['cat']]),
                            'discard_set'       => $this->_abs(':delete|', ['cat' => $this->data['cat']]),
                            'cat_name'          => $cat->name,
                            'sets_count'        => $setsCount,
                            'nested_cats_count' => $nestedCatsCount
                        ],
                        'pluginOptions' => [
                            'resizable' => false
                        ]
                    ]);
                }
            }
        }
    }

    public function rename()
    {
        if ($cat = $this->unpackModel('cat')) {
            $txt = \std\ui\Txt::value($this);

            $cat->name = $txt->value;
            $cat->save();

            $txt->response();

            dataSets()->updatePaths($cat);

            $this->e('ewma/dataSets/cats/update/name', ['cat_id' => $cat->id])->trigger(['cat' => $cat]);
        }
    }

    public function exchangeDialog()
    {
        if ($cat = $this->unpackModel('cat')) {
            $this->c('\std\ui\dialogs~:open:exchange|ewma/dataSets', [
                'default'             => [
                    'pluginOptions/width' => 500
                ],
                'path'                => '\std\data\exchange~:view|ewma/dataSets',
                'data'                => [
                    'target_name' => '#' . $cat->id . ' ' . $cat->path,
                    'import_call' => $this->_abs('~app:import', ['cat' => pack_model($cat)]),
                    'export_call' => $this->_abs('~app:export', ['cat' => pack_model($cat)])
                ],
                'pluginOptions/title' => 'data-sets'
            ]);
        }
    }
}
