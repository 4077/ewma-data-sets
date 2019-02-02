<?php namespace ewma\dataSets\set\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function create()
    {
        if ($set = $this->unxpackModel('set')) {
            $setData = _j($set->data);

            $setData[] = [
                'path' => '',
                'data' => [
                    'type'  => 'string',
                    'value' => [
                        'string' => '',
                        'bool'   => false,
                        'data'   => []
                    ]
                ]
            ];

            $set->data = j_($setData);
            $set->save();

            $this->c('<:reload');
        }
    }

    public function setType()
    {
        if ($set = $this->unxpackModel('set')) {
            $setData = _j($set->data);

            $setData[$this->data('number')]['data']['type'] = $this->data('value');

            $set->data = j_($setData);
            $set->save();

            $this->c('<:reload');
        }
    }

    public function copy()
    {
        if ($set = $this->unxpackModel('set')) {
            $setData = _j($set->data);

            $bufferData = [];
            foreach ($setData as $field) {
                $bufferData[$field['path']] = $field['data']['value'][$field['data']['type']];
            }

            $this->c('\std\ui\data buffer:addArray', $bufferData);

            $this->c('<:reload');
        }
    }

    public function paste()
    {
        if ($set = $this->unxpackModel('set')) {
            $setData = _j($set->data);

            $bufferData = $this->c('\std\ui\data buffer:get');

            foreach ($bufferData as $path => $value) {
                if (is_array($value)) {
                    $type = 'data';
                } else {
                    if (is_bool($value)) {
                        $type = 'bool';
                    } else {
                        $type = 'string';
                    }
                }

                $fieldData = [
                    'path' => $path,
                    'data' => [
                        'type'  => $type,
                        'value' => [
                            'string' => '',
                            'bool'   => false,
                            'data'   => []
                        ]
                    ]
                ];

                ap($fieldData, 'data/value/' . $type, $value);

                $setData[] = $fieldData;
            }

            $set->data = j_($setData);
            $set->save();

            $this->c('<:reload');
        }
    }

    public function duplicate()
    {
        if ($set = $this->unxpackModel('set')) {
            $setData = _j($set->data);

            $setData[] = $setData[$this->data('number')];

            $set->data = j_($setData);
            $set->save();

            $this->c('<:reload');
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/dataSets');
        } else {
            if ($set = $this->unxpackModel('set')) {
                $setData = _j($set->data);

                $field = $setData[$this->data('number')];

                if ($this->data('confirmed')) {
                    $setData = unmap($setData, $this->data('number'));

                    $set->data = j_($setData);
                    $set->save();

                    $this->c('<:reload');
//                    $this->e('gc/sets/delete', ['set_id' => $set->id])->trigger(['set' => $set]);

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/dataSets');
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/dataSets', [
                        'path'            => '\std dialogs/confirm~:view',
                        'data'            => [
                            'confirm_call' => $this->_abs(':delete', $this->data),
                            'discard_call' => $this->_abs(':delete', $this->data),
                            'message'      => 'Удалить поле <b>' . ($field['path'] ? $field['path'] : '...') . '</b> набора <b>' . $set->path . '</b>?'
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

    public function updatePath()
    {
        if ($set = $this->unxpackModel('set')) {
            $txt = \std\ui\Txt::value($this);

            $setData = _j($set->data);

            $setData[$this->data('number')]['path'] = $txt->value;

            $set->data = j_($setData);
            $set->save();

            $txt->response();
        }
    }

    public function updateValue()
    {
        if ($set = $this->unxpackModel('set')) {
            $type = $this->data('type');

            $setData = _j($set->data);

            if ($type == 'string') {
                $txt = \std\ui\Txt::value($this);

                $setData[$this->data('number')]['data']['value']['string'] = $txt->value;

                $set->data = j_($setData);
                $set->save();

                $txt->response();
            }

            if ($type == 'bool') {
                invert($setData[$this->data('number')]['data']['value']['bool']);

                $set->data = j_($setData);
                $set->save();

                $this->c('<:reload');
            }
        }
    }

    public function rearrange()
    {
        if ($set = $this->unxpackModel('set')) {
            $setData = _j($set->data);

            $setData = map($setData, $this->data('sequence'));

            $set->data = j_($setData);
            $set->save();
        }
    }

    public function importDialog()
    {
        $this->c('\std\ui\dialogs~:open:import|std/ui/data', [
            'path'                => '\std\ui\data\import~:view',
            'pluginOptions/title' => ''
        ]);
    }
}
