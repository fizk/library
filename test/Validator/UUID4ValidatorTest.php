<?php

namespace Library\Validator;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UUID4ValidatorTest extends TestCase
{
    public static function valuesProvider(): array
    {
        return [
            ['e46602a4-c49e-4c9c-bed2-0b401f5c0bc8', true],
            ['m46602a4-c49e-4c9c-bed2-0b401f5c0bc8', false],
            ['e46602a4-c49e-4c9c-bed2-0b401f5c0bc',  false],
        ];
    }

    #[DataProvider('valuesProvider')]
    public function testTrue(mixed $input, mixed $output)
    {
        // GIVEN
        $validator = new UUID4Validator();

        // WHEN
        $actual = $output;
        $expected = $validator->isValid($input);

        // THEN
        $this->assertEquals($expected, $actual);
    }
}
