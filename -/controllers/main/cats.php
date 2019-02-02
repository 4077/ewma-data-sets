<?php namespace ewma\dataSets\controllers\main;

class Cats extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $rootNode = $this->getRootNode();

        $v->assign([
                       'CONTENT' => $this->c('\std\ui\tree~:view|' . $this->_nodeId(), [
                           'default'          => [
                               'query_builder' => '>app:getQueryBuilder'
                           ],
                           'node_control'     => [
                               '>node:view',
                               [
                                   'cat'         => '%model',
                                   'root_cat_id' => $rootNode->id
                               ]
                           ],
                           'callbacks'        => [
                               'move' => $this->_abs('>app:moveCallback', [
                                   'cat' => '%source_model'
                               ]),
                               'sort' => $this->_abs('>app:sortCallback', [
                                   'cat' => '%parent_model'
                               ])
                           ],
                           'root_node_id'     => $rootNode->id,
                           'selected_node_id' => $this->s('~:selected_cat_id'),
                           'movable'          => true,
                           'sortable'         => true,
                           'droppable'        => [
                               'set' => [
                                   'accept'         => $this->_selector('@sets:. .set'),
                                   'source_id_attr' => 'set_id',
                                   'path'           => 'set~app:setCat',
                                   'data'           => [
                                       'cat_id' => '%target_id',
                                       'set_id' => '%source_id'
                                   ]
                               ]
                           ],
                           'permissions'      => $this->_module()->namespace . ':~'
                       ])
                   ]);

        $this->css();

        $this->e('ewma/dataSets/cat_select')->rebind(':reload');
        $this->e('ewma/dataSets/cats/create')->rebind(':reload');
        $this->e('ewma/dataSets/cats/delete')->rebind(':reload');
        $this->e('ewma/dataSets/cats/import')->rebind(':reload');

        return $v;
    }

    private function getRootNode()
    {
        if (!$node = \ewma\dataSets\models\Cat::where('parent_id', 0)->first()) {
            $node = \ewma\dataSets\models\Cat::create(['parent_id' => 0]);
        }

        return $node;
    }
}
