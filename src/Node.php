<?php declare(strict_types=1);

namespace Alekitto\BTree;

/**
 * Helper B-tree node data type.
 *
 * @internal
 */
final class Node
{
    public $m;

    /**
     * @var Entry[]
     */
    public $children = [];

    /**
     * Create a Node with $k children.
     *
     * @param int $k
     */
    public function __construct(int $k)
    {
        $this->m = $k;
    }

    public function __clone()
    {
        foreach ($this->children as &$child) {
            $child = new Entry($child->key, $child->val, $child->next ? clone $child->next : null);
        }
    }
}
