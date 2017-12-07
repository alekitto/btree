<?php declare(strict_types=1);

namespace Alekitto\BTree\Tests;

use Alekitto\BTree\BTree;
use PHPUnit\Framework\TestCase;

class BTreeTest extends TestCase
{
    /**
     * @var BTree
     */
    private $tree;

    protected function setUp()
    {
        $this->tree = new BTree();
    }

    public function testClone()
    {
        $this->fillWithTestData($this->tree);
        $copy = clone $this->tree;

        $this->assertCount(22, $copy);
        $this->assertEquals(3, $copy->getHeight());
        $this->assertEquals([
            'www.amazon.com' => '207.171.182.16',
            'www.apple.com' => '17.112.152.32',
            'www.bitbucket.com' => '104.192.143.7',
            'www.cnn.com' => '64.236.16.20',
            'www.dell.com' => '143.166.224.230',
            'www.ebay.com' => '66.135.192.87',
            'www.espn.com' => '199.181.135.201',
            'www.example.org' => '93.184.216.34',
            'www.facebook.com' => '31.13.92.36',
            'www.github.com' => '192.30.253.112',
            'www.gitlab.com' => '104.210.2.228',
            'www.google.com' => '216.239.41.99',
            'www.microsoft.com' => '207.126.99.140',
            'www.nytimes.com' => '199.239.136.200',
            'www.playstation.com' => '23.32.11.42',
            'www.simpsons.com' => '209.052.165.60',
            'www.slashdot.org' => '66.35.250.151',
            'www.sony.com' => '23.33.68.135',
            'www.twitter.com' => '104.244.42.65',
            'www.ubuntu.org' => '82.98.134.233',
            'www.weather.com' => '63.111.66.11',
            'www.yahoo.com' => '216.109.118.65',
        ], $copy->toArray());

        $copy->remove('www.example.org');
        $copy->remove('www.ebay.com');
        $copy->push('www.yahoo.com', 'garbage');

        $this->assertCount(20, $copy);
        $this->assertEquals('garbage', $copy->get('www.yahoo.com'));

        $this->assertCount(22, $this->tree);
        $this->assertEquals('216.109.118.65', $this->tree->get('www.yahoo.com'));
    }

    public function testClear()
    {
        $this->assertCount(0, $this->tree);

        $this->tree->push('foo', 1);
        $this->tree->push('bar', 0);

        $this->assertCount(2, $this->tree);

        $this->tree->clear();
        $this->assertCount(0, $this->tree);
    }

    public function testCount()
    {
        $this->assertCount(0, $this->tree);

        $this->tree->push('foo', 0);
        $this->assertCount(1, $this->tree);

        $this->tree->push('bar', 1);
        $this->assertCount(2, $this->tree);

        $this->tree->remove('foo');
        $this->assertCount(1, $this->tree);
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->tree->isEmpty());

        $this->tree->push('foo', null);
        $this->assertFalse($this->tree->isEmpty());
    }

    public function testGetHeight()
    {
        $this->fillWithTestData($this->tree);
        $this->assertEquals(3, $this->tree->getHeight());
    }

    public function testSearchWithEqualKey()
    {
        $this->fillWithTestData($this->tree);
        $this->assertEquals(['www.github.com', '192.30.253.112'], $this->tree->search('www.github.com'));
        $this->assertNull($this->tree->search('com.nonexistent.local'));
    }

    public function testSearchWithNearestLesserKey()
    {
        $this->fillWithTestData($this->tree);
        $this->assertEquals(['www.facebook.com', '31.13.92.36'], $this->tree->search('www.github', BTree::COMPARISON_LESSER));
    }

    public function testSearchWithNearestGreaterKey()
    {
        $this->fillWithTestData($this->tree);
        $this->assertEquals(['www.github.com', '192.30.253.112'], $this->tree->search('www.github', BTree::COMPARISON_GREATER));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPushShouldThrowIfKeyIsNull()
    {
        $this->tree->push(null, 'value');
    }

    public function testToArray()
    {
        $this->tree->push('foo', 1);
        $this->tree->push('bar', 12);
        $this->tree->push('baz', 1);
        $this->tree->push('foobar', 3);

        $this->assertEquals([
            'bar' => 12,
            'baz' => 1,
            'foo' => 1,
            'foobar' => 3,
        ], $this->tree->toArray());

        $this->assertCount(4, $this->tree);
    }

    private function fillWithTestData(BTree $tree)
    {
        $tree->push('www.example.org', '93.184.216.34');
        $tree->push('www.twitter.com', '104.244.42.65');
        $tree->push('www.facebook.com', '31.13.92.36');
        $tree->push('www.simpsons.com', '209.052.165.60');
        $tree->push('www.apple.com', '17.112.152.32');
        $tree->push('www.amazon.com', '207.171.182.16');
        $tree->push('www.ebay.com', '66.135.192.87');
        $tree->push('www.cnn.com', '64.236.16.20');
        $tree->push('www.google.com', '216.239.41.99');
        $tree->push('www.nytimes.com', '199.239.136.200');
        $tree->push('www.microsoft.com', '207.126.99.140');
        $tree->push('www.ubuntu.org', '82.98.134.233');
        $tree->push('www.sony.com', '23.33.68.135');
        $tree->push('www.playstation.com', '23.32.11.42');
        $tree->push('www.dell.com', '143.166.224.230');
        $tree->push('www.slashdot.org', '66.35.250.151');
        $tree->push('www.github.com', '192.30.253.112');
        $tree->push('www.gitlab.com', '104.210.2.228');
        $tree->push('www.bitbucket.com', '104.192.143.7');
        $tree->push('www.espn.com', '199.181.135.201');
        $tree->push('www.weather.com', '63.111.66.11');
        $tree->push('www.yahoo.com', '216.109.118.65');
    }
}
