<?php declare(strict_types=1);

namespace Alekitto\BTree;

class BTree implements \Countable, \IteratorAggregate
{
    const COMPARISON_EQUAL = 0;
    const COMPARISON_LESSER = -1;
    const COMPARISON_GREATER = 1;

    /**
     * Tree root node.
     *
     * @var Node
     */
    private $root;

    /**
     * Tree depth.
     *
     * @var int
     */
    private $height;

    /**
     * The number of nodes in the BTree.
     *
     * @var int
     */
    private $length;

    public function __construct()
    {
        $this->clear();
    }

    public function __clone()
    {
        $this->root = clone $this->root;
    }

    /**
     * Resets the BTree.
     */
    public function clear(): void
    {
        $this->root = new Node(0);
        $this->height = 0;
        $this->length = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->length;
    }

    /**
     * Checks whether this tree is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return 0 === $this->length;
    }

    /**
     * Gets the height of the BTree (for debugging purposes only).
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Search an entry.
     * Returns a key-value pair with the exact key if found or:
     *  * if comparison is set to COMPARISON_LESSER, the nearest lesser key.
     *  * if comparison is set to COMPARISON_GREATER, the nearest greater key.
     *  * null otherwise.
     *
     * @param mixed $key
     * @param int   $comparison
     *
     * @return null|array Gets a key-value pair if found or null
     */
    public function search($key, $comparison = self::COMPARISON_EQUAL): ?array
    {
        $search = function (Node $node, $key, int $height) use (&$comparison, &$search) {
            $nearest = null;

            if (0 === $height) { // External node
                for ($j = 0; $j < $node->m; ++$j) {
                    $compare = $this->compare($key, $node->children[$j]->key);

                    if (0 === $compare) {
                        return $node->children[$j];
                    } elseif (self::COMPARISON_LESSER === $comparison && 0 < $compare) {
                        $nearest = $node->children[$j];
                    } elseif (self::COMPARISON_GREATER === $comparison && 0 > $compare) {
                        return $node->children[$j];
                    }
                }
            } else { // Internal node
                for ($j = 0; $j < $node->m; ++$j) {
                    if ($j + 1 === $node->m || 0 > $this->compare($key, $node->children[$j + 1]->key)) {
                        $result = $search($node->children[$j]->next, $key, $height - 1);
                        if (null !== $result) {
                            return $result;
                        }
                    }
                }
            }

            return $nearest;
        };

        $result = $search($this->root, $key, $this->height);
        if (null !== $result) {
            return [$result->key, $result->val];
        }

        return null;
    }

    /**
     * Gets the value associated with $key, if set.
     *
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        $found = $this->search($key);
        if ($found) {
            return $found[1];
        }

        return null;
    }

    /**
     * Inserts the key-value pair into the symbol table, overwriting the old value
     * with the new value if the key is already in the symbol table.
     *
     * @param mixed key
     * @param mixed val
     *
     * @throws \InvalidArgumentException if key is null or undefined
     */
    public function push($key, $val): void
    {
        if (null === $key) {
            throw new \InvalidArgumentException('Key cannot be null or undefined');
        }

        $u = $this->insert($this->root, $key, $val, $this->height);
        if (true === $u) {
            return;
        }

        ++$this->length;

        if (! $u) {
            return;
        }

        // Need to split root
        $t = new Node(2);
        $t->children[0] = new Entry($this->root->children[0]->key, null, $this->root);
        $t->children[1] = new Entry($u->children[0]->key, null, $u);

        $this->root = $t;
        ++$this->height;
    }

    /**
     * Removes an entry by key.
     *
     * @param mixed $key
     */
    public function remove($key): void
    {
        $search = function (Node $node, $key, int $height) use (&$search): void {
            if (0 === $height) { // External node
                for ($j = 0; $j < $node->m; ++$j) {
                    if (0 === $this->compare($key, $node->children[$j]->key)) {
                        --$this->length;
                        --$node->m;

                        unset($node->children[$j]);
                        $node->children = array_values($node->children);

                        return;
                    }
                }
            } else { // Internal node
                for ($j = 0; $j < $node->m; ++$j) {
                    if ($j + 1 === $node->m || 0 > $this->compare($key, $node->children[$j + 1]->key)) {
                        $search($node->children[$j]->next, $key, $height - 1);

                        return;
                    }
                }
            }
        };

        $search($this->root, $key, $this->height);
    }

    /**
     * Gets an element array copy (ordered).
     *
     * @return array
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Generator
    {
        $generator = function (Node $h, int $ht) use (&$generator) {
            if (0 === $ht) {
                for ($j = 0; $j < $h->m; ++$j) {
                    yield $h->children[$j]->key => $h->children[$j]->val;
                }
            } else {
                for ($j = 0; $j < $h->m; ++$j) {
                    yield from $generator($h->children[$j]->next, $ht - 1);
                }
            }
        };

        yield from $generator($this->root, $this->height);
    }

    /**
     * Comparison function. Should return an integer value:
     *  - less than 0 if the first argument is smaller than the second
     *  - greater than 0 if the first argument is bigger then the second
     *  - 0 if the two arguments are equals.
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return int
     */
    private function compare($a, $b): int
    {
        return $a <=> $b;
    }

    /**
     * Splits a node.
     *
     * @param Node root
     *
     * @return Node
     */
    private function split(Node $root): Node
    {
        $t = new Node(2);
        $root->m = 2;

        for ($j = 0; 2 > $j; ++$j) {
            $t->children[$j] = $root->children[2 + $j];
        }

        return $t;
    }

    /**
     * Insert/replace a node.
     *
     * @param Node root
     * @param mixed key
     * @param mixed val
     * @param int height
     *
     * @return null|bool|Node
     */
    private function insert($root, $key, $val, $height)
    {
        $newEntry = new Entry($key, $val, null);

        if (0 === $height) { // External node
            for ($j = 0; $j < $root->m; ++$j) {
                $compare = $this->compare($key, $root->children[$j]->key);
                if (0 === $compare) {
                    $root->children[$j]->val = $val;

                    return true;
                }

                if (0 > $compare) {
                    break;
                }
            }
        } else { // Internal node
            for ($j = 0; $j < $root->m; ++$j) {
                if (($j + 1 === $root->m) || 0 > $this->compare($key, $root->children[$j + 1]->key)) {
                    $u = $this->insert($root->children[$j++]->next, $key, $val, $height - 1);
                    if (! $u || true === $u) {
                        return $u;
                    }

                    $newEntry->key = $u->children[0]->key;
                    $newEntry->next = $u;
                    break;
                }
            }
        }

        for ($i = $root->m; $i > $j; --$i) {
            $root->children[$i] = $root->children[$i - 1];
        }

        $root->children[$j] = $newEntry;
        ++$root->m;

        if (4 > $root->m) {
            return null;
        }

        return $this->split($root);
    }
}
