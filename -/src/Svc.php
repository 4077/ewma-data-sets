<?php namespace ewma\dataSets;

class Svc extends \ewma\service\Service
{
    /**
     * @var self
     */
    public static $instance;

    /**
     * @return \ewma\dataSets\Svc
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self;
            static::$instance->__register__();
        }

        return static::$instance;
    }

    protected $services = ['cats'];

    /**
     * @var $cats \ewma\dataSets\Svc\Cats
     */
    public $cats = \ewma\dataSets\Svc\Cats::class;

    //
    //
    //

    public function updatePaths($cat = false)
    {
        if (!$cat) {
            $cat = \ewma\dataSets\models\Cat::where('parent_id', 0)->first();
        }

        $tree = \ewma\Data\Tree::get($cat);

        $this->updatePathsRecursion($tree, $cat);
    }

    private function updatePathsRecursion(\ewma\Data\Tree $tree, $cat)
    {
        $branch = $tree->getBranch($cat);
        $segments = table_cells_by_id($branch, 'name');
        array_shift($segments);
        $path = a2p($segments);

        $cat->path = $path;
        $cat->save();

        $cat->sets->each(function ($set) use ($cat, $path) {
            $set->path = $path . ':' . $set->name;
            $set->cat_position = $cat->position;
            $set->save();
        });

        $subnodes = $tree->getSubnodes($cat->id);
        foreach ($subnodes as $subnode) {
            self::updatePathsRecursion($tree, $subnode);
        }
    }

    private $outputs = [];

    /**
     * @param $path string path/to/cat[:setName[:fieldName]]
     *
     * @return mixed
     */
    public function get($path)
    {
        list($catPath, $setName, $fieldPath, $nodePath) = array_pad(explode(':', $path), 4, null);

        if (null !== $setName) {
            if (!isset($this->outputs[$catPath][$setName])) {
                if ($set = $this->getSet($catPath, $setName)) {
                    $this->outputs[$catPath][$setName] = $this->getSetData($set);
                } else {
                    $this->outputs[$catPath][$setName] = false;

                    appc()->console('data set with path "' . $catPath . ':' . $setName . '" not exists');
                }
            }

            if ($this->outputs[$catPath][$setName]) {
                if (null !== $fieldPath) {
                    if (isset($this->outputs[$catPath][$setName][$fieldPath])) {
                        if (null !== $nodePath) {
                            return ap($this->outputs[$catPath][$setName][$fieldPath], $nodePath);
                        } else {
                            return $this->outputs[$catPath][$setName][$fieldPath];
                        }
                    } else {
                        appc()->console('field "' . $fieldPath . '" not isset in data set with path "' . $catPath . ':' . $setName . '"');
                    }
                } else {
                    return $this->outputs[$catPath][$setName];
                }
            }
        } else {
            $this->outputs[$catPath] = $this->getCatSets($catPath);

            return $this->outputs[$catPath];
        }
    }

    private $sets = [];

    private function getSet($catPath, $setName)
    {
        if (!isset($this->sets[$catPath][$setName])) {
            $this->sets[$catPath][$setName] = \ewma\dataSets\models\Set::where('path', $catPath . ':' . $setName)->orderBy('cat_position')->orderBy('position')->first() or
            $this->sets[$catPath][$setName] = false;
        }

        return $this->sets[$catPath][$setName];
    }

    private function getCatSets($catPath)
    {
        if ($cat = \ewma\dataSets\models\Cat::where('path', $catPath)->first()) {
            $sets = $cat->sets()->orderBy('position', 'DESC')->get();

            foreach ($sets as $set) {
                $this->sets[$catPath][$set->name] = $this->getSetData($set);
            }
        }

        return $this->sets[$catPath];
    }

    private function getSetData($set)
    {
        if ($set->data) {
            $data = _j($set->data);

            $setData = array_reverse(array_combine(array_reverse(array_column($data, 'path')), array_reverse(array_column($data, 'data'))));

            $output = [];
            foreach ($setData as $fieldPath => $fieldData) {
                $value = $fieldData['value'][$fieldData['type']];

                if ($fieldPath) {
                    ap($output, $fieldPath, $fieldData['value'][$fieldData['type']]);
                } else {
                    $output[''] = $value;
                }
            }

            return $output;
        } else {
            return [];
        }
    }
}