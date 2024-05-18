<?php

namespace Library\Filter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ToIntTest extends TestCase
{
    public static function valuesProvider(): array
    {
        return [
            ['1', 1],
            [' 1 ', 1],
            [1, 1],
            ['A', null],
            ['1_2', null],
        ];
    }

    #[DataProvider('valuesProvider')]
    public function testTrue(mixed $input, mixed $output)
    {
        // GIVEN
        $filter = new ToInt();

        // WHEN
        $expected = $output;
        $actual = $filter->filter($input);

        // THEN
        $this->assertEquals($expected, $actual);
    }
}
