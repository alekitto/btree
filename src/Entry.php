<?php declare(strict_types=1);

namespace Alekitto\BTree;

/**
 * @internal
 */
final class Entry
{
    /**
     * Entry key.
     *
     * @var mixed
     */
    public $key;

    /**
     * Entry value.
     *
     * @var mixed
     */
    public $val;

    /**
     * Helper field to iterate over entries.
     *
     * @var Node
     */
    public $next;

    public function __construct($key, $val, ?Node $next)
    {
        $this->key = $key;
        $this->val = $val;
        $this->next = $next;
    }
}
