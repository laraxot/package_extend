<?php



/**
 * @author Brazenvoid
 * Class JSTreeJSONBuilder
 * http://programming-tid-bits.blogspot.it/2015/08/laravel-helper-library-for-jstree-json.html
 * ---------------------------------------------------------------------------------------------------------------------
 * Properties
 * ---------------------------------------------------------------------------------------------------------------------
 * > records        - array|Model[]|Model   - The array containing Models or Models converted to array
 *                                          - For full tree generation this should contain the roots
 *
 * > skip           - Closure               - Condition upon which the node will be skipped
 *
 * > skipParentIds  - array                 - Array of parent ids to skip
 *
 * > id             - string|Closure        - The column name which contains the id of the node
 *
 * > text           - string|Closure        - The column name which contains the text for the node
 *
 * > defaultIcon    - string                - A link to an icon or css class which will be assigned when the same is not
 *                                            retrieved through any defined sources
 *
 * > opened         - bool|Closure          - The node(s) should be expanded showing all its children
 *
 * > disabled       - bool|Closure          - The node(s) should appear disabled
 *
 * > selected       - bool|Closure          - The node(s) should appear selected
 *
 * > data           - string|Closure|null   - Any other column data to embed or through another source for each node
 *                  - array                 - Same data to embed for all nodes
 *
 * > removeDuplicates - bool                - Remove any duplicate nodes
 *
 * > returnIds      - array                 - After running a shaper, the node ids can be retrieved from this array,
 *                                            they are unsorted so they are in order of creation of nodes
 *
 * > convert        - bool                  - Signifies whether the data is to be finalized as json or not
 *
 * Notes:
 * > Closures can be used to change the field value per node. The node's data array/object is passed to the closure.
 *
 * ---------------------------------------------------------------------------------------------------------------------
 * Setters
 * ---------------------------------------------------------------------------------------------------------------------
 * > setIcon(class = true, icon = null)
 * - icon           - string|Closure|null   - This must in any form return null or the icon link or css class name
 * - class          - bool                  - This must be true, if icon is a css class name or link
 *
 * > setNextNodes(column = false, parent = null)
 * - nextNodes      - string|Closure|null   - For simple list it should be the parent id and it can be a string or a
 *                                            closure
 *                                          - For child to parent trees it must be a closure and should return the
 *                                            parent node
 *                                          - For full trees it must be a closure and must return the next child node
 * - column         - bool                  - This must be true, if parent is a column name
 *
 * > setData(column = true, data = null, default = null)
 * - column         - bool                  - Signifies that the string passed in data is actually a column name
 * - data           - string|Closure|null   - A string or closure to make the data value
 * - default        - string|null           - A default value, if the system fails to find any value in passed data
 *
 * ---------------------------------------------------------------------------------------------------------------------
 * Tree Builders
 * ---------------------------------------------------------------------------------------------------------------------
 * > shapeSimpleList()
 * Generates a single level of tree at a time.
 * Can be used to build full trees by passing the whole table and assigning parent ids through a closure or a column.
 *
 * > shapeChildToParentBranches()
 * Generates a tree from given children to the roots, thus skipping any siblings of the parents.
 *
 * > shapeParentToChildBranches()
 * Generates a tree from roots to their leaves i.e. a full tree.
 *
 * ---------------------------------------------------------------------------------------------------------------------
 */
class JSTreeJSONBuilder
{
    /**
     * @var array|\Illuminate\Database\Eloquent\Collection
     */
    public $records = [];
    public $skip;
    public $skipParentIds = [];
    public $id = '';
    public $text = '';
    public $opened = false;
    public $disabled = false;
    public $selected = false;
    public $convert = true;
    public $returnIds = [];
    public $removeDuplicates = false;

    public $defaultIcon;
    public $defaultData;

    private $data;
    private $dataColumn = true;

    private $icon = '';
    private $iconClass = true;

    private $nextNodes = '';
    private $nextNodesColumn = false;
    private $resolveNextNodes = false;

    /**
     * @param Closure|string|null $data
     * @param bool                $column
     * @param string|null         $default
     */
    public function setData($column = true, $data = null, $default = null)
    {
        $this->defaultData = $default;
        $this->data = $data;
        $this->dataColumn = $column;
    }

    /**
     * @param Closure|string|null $icon
     * @param bool                $class
     * @param string|null         $default
     */
    public function setIcon($class = true, $icon = null, $default = null)
    {
        $this->defaultIcon = $default;
        $this->icon = $icon;
        $this->iconClass = $class;
    }

    /**
     * @param bool                $column
     * @param Closure|string|null $nodes
     */
    public function setNextNodes($column = false, $nodes = null)
    {
        $this->nextNodes = $nodes;
        $this->nextNodesColumn = $column;
    }

    /**
     * @return array|string
     */
    public function shapeSimpleList()
    {
        $tree = [];
        $this->returnIds = [];
        foreach ($this->records as $node) {
            if (!Utilities::getMixedOrCall($this->skip, $node, false)) {
                $nextNode = $this->buildTreeNodeJSON($node, $this->getParent($node, true));
                if (null != $nextNode) {
                    $tree[] = $nextNode;
                }
            }
        }

        return $this->convert ? \json_encode($tree) : $tree;
    }

    /**
     * @param array|object $node
     * @param string       $parentId
     *
     * @return array
     */
    private function buildTreeNodeJSON($node, $parentId)
    {
        $id = Utilities::getStructureValueOrCall($this->id, $node);
        if ($this->removeDuplicates && \in_array($id, $this->returnIds, true)) {
            return null;
        }
        if (\in_array($id, $this->skipParentIds, true)) {
            return null;
        }
        if (\in_array($parentId, $this->skipParentIds, true)) {
            $parentId = '#';
        }
        $node = ['id' => $id, 'parent' => $parentId, 'text' => Utilities::getStructureValueOrCall($this->text, $node), 'icon' => $this->getIcon($node), 'data' => $this->getData($node), 'state' => ['opened' => Utilities::getMixedOrCall($this->opened, $node), 'disabled' => Utilities::getMixedOrCall($this->disabled, $node), 'selected' => Utilities::getMixedOrCall($this->selected, $node)]];
        $this->returnIds[] = $node['id'];

        return $node;
    }

    /**
     * @param array|Eloquent $node
     *
     * @return mixed|string
     */
    private function getIcon($node)
    {
        if ($this->iconClass) {
            return $this->icon;
        } else {
            $icon = Utilities::getStructureValueOrCall($this->icon, $node);

            return empty($icon) ? $this->defaultIcon : $icon;
        }
    }

    /**
     * @param array|Eloquent $node
     *
     * @return mixed|string
     */
    private function getData($node)
    {
        if ($this->dataColumn) {
            return Utilities::getStructureValueOrCall($this->data, $node, $this->defaultData);
        } else {
            $data = Utilities::getMixedOrCall($this->data, $node, $this->defaultData);

            return empty($data) ? $this->defaultData : $data;
        }
    }

    /**
     * @param array|Eloquent $node
     * @param bool           $closureReturnsParentId
     *
     * @return mixed|string
     */
    private function getParent($node, $closureReturnsParentId)
    {
        if ($this->nextNodesColumn) {
            $parent = Utilities::getStructureValueOrCall($this->nextNodes, $node, '#');
            if (empty($parent)) {
                return ($this->resolveNextNodes) ? [$this->id => '#'] : '#';
            } else {
                return ($this->resolveNextNodes) ? $node->newInstance()->find($parent) : $parent;
            }
        } else {
            $parent = Utilities::getMixedOrCall($this->nextNodes, $node, '#');
            if ($closureReturnsParentId) {
                return (null == $parent) ? '#' : $parent;
            } else {
                return empty($parent) ? [$this->id => '#'] : $parent;
            }
        }
    }

    /**
     * @return array
     */
    public function shapeChildToParentBranches()
    {
        $tree = [];
        $this->returnIds = [];
        $this->resolveNextNodes = true;
        foreach ($this->records as $leaf) {
            $parents = $this->buildParentBranches($leaf);
            if (!empty($parents)) {
                $tree = \array_merge($tree, $parents);
            }
        }

        return $this->convert ? \json_encode($tree) : $tree;
    }

    /**
     * @param array|Eloquent $node
     *
     * @return array
     */
    private function buildParentBranches($node)
    {
        $tree = [];
        if (!Utilities::getMixedOrCall($this->skip, $node, false)) {
            $parent = $this->getParent($node, false);
            $nextNode = $this->buildTreeNodeJSON($node, Utilities::getStructureValueOrCall($this->id, $parent));
            if (null != $nextNode) {
                $tree[] = $nextNode;
            }

            return ('#' == $parent[$this->id]) ? $tree : \array_merge($tree, $this->buildParentBranches($parent));
        } else {
            return $tree;
        }
    }

    /**
     * @return array
     */
    public function shapeParentToChildBranches()
    {
        $tree = [];
        $this->returnIds = [];
        foreach ($this->records as $root) {
            $children = $this->buildChildBranches($root, '#');
            if (!empty($children)) {
                $tree = \array_merge($tree, $children);
            }
        }

        return $this->convert ? \json_encode($tree) : $tree;
    }

    /**
     * @param array|Eloquent $node
     * @param mixed          $parentId
     *
     * @return array
     */
    private function buildChildBranches($node, $parentId)
    {
        $tree = [];
        if (!Utilities::getMixedOrCall($this->skip, $node, false)) {
            $id = Utilities::getStructureValueOrCall($this->id, $node);
            $nextNode = $this->buildTreeNodeJSON($node, $parentId);
            if (null != $nextNode) {
                $tree[] = $nextNode;
            }
            foreach ($this->getChildren($node) as $child) {
                $tree = \array_merge($tree, $this->buildChildBranches($child, $id));
            }
        }

        return $tree;
    }

    /**
     * @param array|Eloquent $node
     *
     * @return mixed|string
     */
    private function getChildren($node)
    {
        if ($this->nextNodesColumn) {
            return $node->newInstance()->where($this->nextNodes, '=', $node->getKey())->get();
        } else {
            return Utilities::getMixedOrCall($this->nextNodes, $node, []);
        }
    }
}
