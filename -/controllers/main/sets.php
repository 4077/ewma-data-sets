<?php namespace ewma\dataSets\controllers\main;

class Sets extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        if ($cat = \ewma\dataSets\models\Cat::find($this->s('~:selected_cat_id'))) {
            $sets = $cat->sets()->orderBy('position')->get();

            $selectedSetId = $this->s('~:selected_set_id_by_cat_id/' . $cat->id);

            foreach ($sets as $set) {
                $setXPack = xpack_model($set);

                $selector = $this->_selector(". .set[set_id='" . $set->id . "']");

                $v->assign('set', [
                    'ID'               => $set->id,
                    'SELECTED_CLASS'   => $set->id == $selectedSetId ? 'selected' : '',
                    'NAME'             => $this->c('\std\ui txt:view', [
                        'path'                => '>xhr:rename',
                        'data'                => [
                            'set' => $setXPack
                        ],
                        'class'               => 'txt',
                        'fitInputToClosest'   => '.set',
                        'placeholder'         => '...',
                        'editTriggerSelector' => $selector . " .rename.button",
                        'content'             => $set->name
                    ]),
                    'RENAME_BUTTON'    => $this->c('\std\ui tag:view', [
                        'attrs'   => [
                            'class' => 'rename button',
                            'hover' => 'hover',
                            'title' => 'Переименовать'
                        ],
                        'content' => '<div class="icon"></div>'
                    ]),
                    'DUPLICATE_BUTTON' => $this->c('\std\ui button:view', [
                        'path'    => '>xhr:duplicate',
                        'data'    => [
                            'set' => $setXPack
                        ],
                        'class'   => 'duplicate button',
                        'title'   => 'Дублировать',
                        'content' => '<div class="icon"></div>'
                    ]),
                    'DELETE_BUTTON'    => $this->c('\std\ui button:view', [
                        'path'    => '>xhr:delete',
                        'data'    => [
                            'set' => $setXPack
                        ],
                        'class'   => 'button delete',
                        'title'   => 'Удалить',
                        'content' => '<div class="icon"></div>'
                    ])
                ]);

                $this->c('\std\ui button:bind', [
                    'selector' => $selector,
                    'path'     => '>xhr:select',
                    'data'     => [
                        'set' => $setXPack
                    ]
                ]);
            }

            $this->c('\std\ui sortable:bind', [
                'selector'       => $this->_selector(),
                'items_id_attr'  => 'set_id',
                'path'           => '>xhr:rearrange',
                'plugin_options' => [
                    'distance' => 20
                ]
            ]);

            $this->c('\std\ui droppable:bind', [
                'selector'       => $this->_selector(),
                'target_id_attr' => 'set_id',
                'accept'         => $this->_selector('set~:. .field'),
                'source_id_attr' => 'field_id',
                'path'           => '>xhr:moveField',
                'data'           => [

                ]
            ]);

            $v->assign([
                           'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:create',
                               'data'    => [
                                   'cat' => xpack_model($cat)
                               ],
                               'class'   => 'create_button',
                               'content' => 'Создать'
                           ])
                       ]);

            $this->e('ewma/dataSets/cat_select')->rebind(':reload');
            $this->e('ewma/dataSets/select')->rebind(':reload');

            $this->e('ewma/dataSets/create')->rebind(':reload');
            $this->e('ewma/dataSets/delete')->rebind(':reload');

            $this->e('ewma/dataSets/update/cat')->rebind(':reload');
        }

        $this->css(':\css\std~, \js\jquery\ui icons');

        return $v;
    }
}
