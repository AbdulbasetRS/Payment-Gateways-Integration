<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Unit\Utils;

use Abdulbaset\PaymentGatewaysIntegration\Utils\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testFirstWhere()
    {
        $items = [
            ['rel' => 'self', 'href' => 'http://example.com/self'],
            ['rel' => 'approve', 'href' => 'http://example.com/approve'],
            ['rel' => 'cancel', 'href' => 'http://example.com/cancel'],
        ];

        $collection = new Collection($items);

        $result = $collection->firstWhere('rel', 'approve');
        $this->assertEquals('http://example.com/approve', $result['href']);

        $notFound = $collection->firstWhere('rel', 'nonexistent');
        $this->assertNull($notFound);
    }

    public function testMakeStaticConstructor()
    {
        $items = [['key' => 'value']];
        $collection = Collection::make($items);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($items, $collection->toArray());
    }

    public function testToArray()
    {
        $items = [['key' => 'value']];
        $collection = new Collection($items);

        $this->assertEquals($items, $collection->toArray());
    }
}
