<?php namespace ewma\dataSets\Svc;

class Cats extends \ewma\service\Service
{
    protected $services = ['svc'];

    /**
     * @var $svc \ewma\dataSets\Svc
     */
    public $svc = \ewma\dataSets\Svc::class;

    //
    //
    //

    public function create(\ewma\dataSets\models\Cat $cat)
    {
        return $cat->nested()->create([]);
    }

    public function duplicate(\ewma\dataSets\models\Cat $cat)
    {
        $tree = \ewma\Data\Tree::get(
            \ewma\dataSets\models\Cat::orderBy('position')
        );

        $newCat = $this->duplicateRecursion($tree, $cat);

        return $newCat;
    }

    private function duplicateRecursion(\ewma\Data\Tree $tree, $cat, $parentCat = null)
    {
        $newCatData = $cat->toArray();
        if (null !== $parentCat) {
            $newCatData['parent_id'] = $parentCat->id;
        }

        $newCat = \ewma\dataSets\models\Cat::create($newCatData);

        $sets = $cat->sets()->orderBy('position')->get();
        foreach ($sets as $set) {
            $newCat->sets()->create($set->toArray());
        }

        $subcats = $tree->getSubnodes($cat->id);
        foreach ($subcats as $subcat) {
            $this->duplicateRecursion($tree, $subcat, $newCat);
        }

        return $newCat;
    }

    public function delete(\ewma\dataSets\models\Cat $cat)
    {
        $catsIds = \ewma\Data\Tree::getIds($cat);

        \ewma\dataSets\models\Cat::whereIn('id', $catsIds)->delete();
        \ewma\dataSets\models\Set::whereIn('cat_id', $catsIds)->delete();
    }

    private $exportOutput = [];

    public function export(\ewma\dataSets\models\Cat $cat)
    {
        $tree = \ewma\Data\Tree::get(\ewma\dataSets\models\Cat::orderBy('position'));

        $this->exportOutput['cat_id'] = $cat->id;
        $this->exportOutput['cats'] = $tree->getFlattenData($cat->id);

        $this->exportRecursion($tree, $cat);

        return $this->exportOutput;
    }

    private function exportRecursion(\ewma\Data\Tree $tree, \ewma\dataSets\models\Cat $cat)
    {
        $sets = $cat->sets()->orderBy('position')->get();
        foreach ($sets as $set) {
            $this->exportOutput['sets'][$cat->id][] = $set->toArray();
        }

        $subcats = $tree->getSubnodes($cat->id);
        foreach ($subcats as $subcat) {
            $this->exportRecursion($tree, $subcat);
        }
    }

    public function import(\ewma\dataSets\models\Cat $target, $data, $skipFirstLevel = false)
    {
        $this->importRecursion($target, $data, $data['cat_id'], $skipFirstLevel);

        dataSets()->updatePaths($target);
    }

    private function importRecursion(\ewma\dataSets\models\Cat $target, $importData, $catId, $skipFirstLevel = false)
    {
        $newCatData = $importData['cats']['nodes_by_id'][$catId];

        if ($skipFirstLevel) {
            $newCat = $target;
        } else {
            $newCat = $target->nested()->create($newCatData);
        }

        if (!empty($importData['sets'][$catId])) {
            foreach ($importData['sets'][$catId] as $newSetData) {
                $newCat->sets()->create($newSetData);
            }
        }

        if (!empty($importData['cats']['ids_by_parent'][$catId])) {
            foreach ($importData['cats']['ids_by_parent'][$catId] as $sourceCatId) {
                $this->importRecursion($newCat, $importData, $sourceCatId);
            }
        }
    }
}
