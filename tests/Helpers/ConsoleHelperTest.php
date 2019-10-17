<?php

namespace Slides\Saml2\Tests;

use PHPUnit\Framework\TestCase;
use Slides\Saml2\Helpers\ConsoleHelper;

class ConsoleHelperTest extends TestCase
{
    public function testStringToArray()
    {
        static::assertEquals([], ConsoleHelper::stringToArray(''));
        static::assertEquals([], ConsoleHelper::stringToArray(null));

        static::assertEquals(
            ['item1' => 'value1', 'item2' => 'value2'],
            ConsoleHelper::stringToArray('item1:value1,item2:value2')
        );

        static::assertEquals(
            ['item1' => 'value1', 'item2' => 'value 2'],
            ConsoleHelper::stringToArray(' item1 :value1 , item2 :value 2')
        );

        static::assertEquals(
            ['value1', 'value2', 'value3'],
            ConsoleHelper::stringToArray('value1,value2,value3')
        );
    }
}