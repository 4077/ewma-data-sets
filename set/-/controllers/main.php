<?php namespace ewma\dataSets\set\controllers;

class Main extends \Controller
{
    private $set;

    public function __create()
    {
        $sMaster = $this->s('^');

        $setId = ap($sMaster, 'selected_set_id_by_cat_id/' . $sMaster['selected_cat_id']);

        $this->set = \ewma\dataSets\models\Set::find($setId);
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        if ($set = $this->set) {
            $setPack = pack_model($set);
            $setXPack = xpack_model($set);

            $fields = _j($set->data);

            foreach ((array)$fields as $number => $field) {
                $v->assign('field', [
                    'NUMBER'           => $number,
                    'ID'               => $set->id . ':' . $number,
                    'PATH'             => $this->c('\std\ui txt:view', [
                        'path'              => '>xhr:updatePath',
                        'data'              => [
                            'set'    => $setXPack,
                            'number' => $number
                        ],
                        'class'             => 'txt',
                        'fitInputToClosest' => '.path',
                        'content'           => $field['path']
                    ]),
                    'VALUE'            => $this->valueView($number, $field, $setPack, $setXPack),
                    'TYPE_SELECT'      => $this->c('\std\ui select:view', [
                        'path'     => '>xhr:setType',
                        'data'     => [
                            'set'    => $setXPack,
                            'number' => $number
                        ],
                        'class'    => 'type_select',
                        'items'    => ['string', 'bool', 'data'],
                        'combine'  => true,
                        'selected' => $field['data']['type']
                    ]),
                    'DUPLICATE_BUTTON' => $this->c('\std\ui button:view', [
                        'path'    => '>xhr:duplicate',
                        'data'    => [
                            'set'    => $setXPack,
                            'number' => $number
                        ],
                        'class'   => 'duplicate button',
                        'title'   => 'Дублировать',
                        'content' => '<div class="icon"></div>'
                    ]),
                    'DELETE_BUTTON'    => $this->c('\std\ui button:view', [
                        'path'    => '>xhr:delete',
                        'data'    => [
                            'set'    => $setXPack,
                            'number' => $number
                        ],
                        'class'   => 'button delete',
                        'title'   => 'Удалить',
                        'content' => '<div class="icon"></div>'
                    ])
                ]);
            }

            $this->c('\std\ui sortable:bind', [
                'selector'       => $this->_selector() . ' .fields',
                'items_id_attr'  => 'number',
                'path'           => '>xhr:rearrange',
                'data'           => [
                    'set' => $setXPack
                ],
                'plugin_options' => [
                    'distance' => 20
                ]
            ]);

            $v->assign([
                           'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:create',
                               'class'   => 'create_button',
                               'data'    => [
                                   'set' => $setXPack
                               ],
                               'content' => 'Создать'
                           ]),
                           'COPY_BUTTON'   => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:copy',
                               'class'   => 'copy_button',
                               'data'    => [
                                   'set' => $setXPack
                               ],
                               'content' => 'Копировать'
                           ]),
                           'PASTE_BUTTON'  => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:paste',
                               'class'   => 'paste_button',
                               'data'    => [
                                   'set' => $setXPack
                               ],
                               'content' => 'Вставить'
                           ]),
                           'IMPORT_BUTTON' => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:importDialog',
                               'class'   => 'import_button',
                               'data'    => [
                                   'set' => $setXPack
                               ],
                               'content' => 'Импорт'
                           ])
                       ]);

            $this->e('ewma/dataSets/delete', ['set_id' => $set->id])->rebind(':reload');
        }

        $this->css(':\css\std~, \js\jquery\ui icons');

        $this->c('\std\ui\data~svc:addDialogsContainer');

        $this->e('ewma/dataSets/cat_select')->rebind(':reload');
        $this->e('ewma/dataSets/select')->rebind(':reload');
        $this->e('ewma/dataSets/create')->rebind(':reload');

        return $v;
    }

    private function valueView($number, $field, $setPack, $setXPack)
    {
        if ($field['data']['type'] == 'string') {
            return $this->c('\std\ui txt:view', [
                'path'              => '>xhr:updateValue',
                'data'              => [
                    'set'    => $setXPack,
                    'number' => $number,
                    'type'   => 'string'
                ],
                'class'             => 'txt',
                'fitInputToClosest' => '.value',
                'content'           => $field['data']['value']['string']
            ]);
        }

        if ($field['data']['type'] == 'bool') {
            return $this->c('\std\ui button:view', [
                'path'    => '>xhr:updateValue',
                'data'    => [
                    'set'    => $setXPack,
                    'number' => $number,
                    'type'   => 'bool'
                ],
                'class'   => 'bool_value_button ' . ($field['data']['value']['bool'] ? 'true' : 'false'),
                'content' => $field['data']['value']['bool'] ? 'true' : 'false',
            ]);
        }

        if ($field['data']['type'] == 'data') {
            return $this->c('\std\ui\data~:view|' . $this->_nodeInstance() . '/' . $number, [
                'read_call'  => $this->_abs('>app:readData', [
                    'set'    => $setPack,
                    'number' => $number
                ]),
                'write_call' => $this->_abs('>app:writeData', [
                    'set'    => $setPack,
                    'number' => $number
                ]),
                'reset'      => true
            ]);
        }
    }
}
