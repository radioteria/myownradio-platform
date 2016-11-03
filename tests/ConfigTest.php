<?php

namespace myownradio\tests;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfig()
    {
        $this->assertEquals(1, config('foo.a'));
        $this->assertEquals(2, config('foo.b'));
        $this->assertEquals(100, config('foo.deep.baz'));
    }
}
