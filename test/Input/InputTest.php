<?php

namespace Library\Input;

use PHPUnit\Framework\TestCase;
use Laminas\Validator\{
    NotEmpty,
    EmailAddress,
    Ip,
    StringLength
};
use Laminas\Filter\{
    UpperCaseWords,
    PregReplace,
    StringToLower
};
use Laminas\Filter\Word\CamelCaseToDash;

class InputTest extends TestCase
{
    public function testValidatesEmailAndUppercasesWord()
    {
        // GIVEN
        $input = (new Input('email'))
            ->attachFilter(new UpperCaseWords())
            ->attachValidator(new EmailAddress())
        ;
        $value = 'hundur@hundur.is';

        // WHEN
        $isValid = $input->isValid($value);

        // THEN
        $expected = 'Hundur@hundur.is';
        $actual = $input->getValue();

        $this->assertTrue($isValid);
        $this->assertEquals($expected, $actual);
    }

    public function testNotAValidEmailAddress()
    {
        // GIVEN
        $input = (new Input('email'))
            ->attachFilter(new UpperCaseWords())
            ->attachValidator(new EmailAddress())
        ;

        // WHEN
        $isValid = $input->isValid('hundur');

        // THEN
        $expected = 'Hundur';
        $actual = $input->getValue();
        $messages = $input->getMessages();

        $this->assertFalse($isValid);
        $this->assertEquals($expected, $actual);
        $this->assertCount(1, $messages);
    }

    public function testThatValidateHasBeenCalled()
    {
        $this->expectException(InputException::class);

        // GIVEN
        $input = (new Input('email'))
            ->attachFilter(new UpperCaseWords())
            ->attachValidator(new EmailAddress())
        ;
        // WHEN
        $input->getValue();
        $input->getMessages();

        // THEN
    }

    public function testNotAllowEmptyValues()
    {
        // GIVEN
        $input = (new Input('email'))
            ->attachValidator(new NotEmpty())
        ;
        $value = '';

        // WHEN
        $isValid = $input->isValid($value);

        // THEN
        $this->assertFalse($isValid);
        $this->assertArrayHasKey('isEmpty', $input->getMessages()[0]);
    }

    public function testMultipleFilters()
    {
        // GIVEN
        $input = (new Input('string'))
            ->attachFilter(new CamelCaseToDash())
            ->attachFilter(new StringToLower())
            ->attachFilter(new PregReplace(['pattern' => '/[fF]iltered/', 'replacement' => 'formatted']))
        ;
        $value = 'ThisIsTheStringToBeFiltered';

        // WHEN
        $input->isValid($value);

        // THEN
        $expected = 'this-is-the-string-to-be-formatted';
        $actual = $input->getValue();
        $this->assertEquals($expected, $actual);
    }

    public function testValidationMessages()
    {
        // GIVEN
        $input = (new Input('string'))
            ->attachValidator(new EmailAddress())
            ->attachValidator(new StringLength(['max' => 6]))
            ->attachValidator(new Ip())
        ;
        $value = 'email@address.com';

        // WHEN
        $input->isValid($value);

        // THEN
        $expected = 2;
        $actual = $input->getMessages();
        $this->assertCount($expected, $actual);
        $this->assertArrayHasKey('stringLengthTooLong', $actual[0]);
        $this->assertArrayHasKey('notIpAddress', $actual[1]);
    }

    public function testValidationNoMessages()
    {
        // GIVEN
        $input = (new Input('string'))
            ->attachValidator(new EmailAddress())
            ->attachValidator(new StringLength(['min' => 6]))
        ;
        $value = 'email@address.com';

        // WHEN
        $input->isValid($value);

        // THEN
        $expected = 0;
        $actual = $input->getMessages();
        $this->assertCount($expected, $actual);
    }
}
